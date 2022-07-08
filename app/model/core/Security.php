<?php

use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Crypt\RSA;

/**
 * Статичный класс Security.
 *
 * Набор методов для решения вспомогательных задач безопасности.
 *
 * @author Dmitriy Lunin
 * @author Yii2
 * @author and other...
 */
class Security
{
    /** Минимальная длина пароля для проверки надёжности */
    public const PASSWORD_MIN_LEN = 8;

    /**
     * @see Security::decrypt()
     */
    public const CIPHERTEXT_SEPARATOR = '*';

    /**
     * @see Security::calculatePasswordHash()
     */
    protected const PASSWORD_HASH_COST = 13;

    /**
     * Шифрование данных по алгоритму RSA.
     *
     * @param   string  $plaintext  Исходные данные
     * @return  string
     */
    public static function encrypt(string $plaintext): string
    {
	    $Key = PublicKeyLoader::load(Cfg::RSA_PUBLIC_KEY);
	    $Key = $Key->withPadding(RSA::ENCRYPTION_PKCS1);

	    return base64_encode( $Key->encrypt($plaintext) );
    }

    /**
     * Расшифровка данных по алгоритму RSA.
     *
     * В тех случаях, когда необходимо зашифровать/расшифровать данные,
     * объём которых превышает максимально допустимый для используемого ключа,
     * данные разбиваются на части. Эти части шифруются отдельно, а результаты
     * объединяются в одну строку с использованием разделителя - [[self::CIPHERTEXT_SEPARATOR]].
     *
     * @param   string  $ciphertext  Зашифрованные данные
     * @return  string
     */
    public static function decrypt(string $ciphertext): string
    {
        if (empty($ciphertext)) {
            throw new InvalidArgumentException('Ciphertext cannot be empty');
        }

	    if ($ciphertext === 'false') {
		    throw new InvalidArgumentException('PUBLIC KEY is invalid');
	    }

	    $Key = PublicKeyLoader::load(Cfg::RSA_PRIVATE_KEY);
	    $Key = $Key->withPadding(RSA::ENCRYPTION_PKCS1);

	    $plaintext = '';
	    $parts = explode(self::CIPHERTEXT_SEPARATOR, $ciphertext);

	    foreach ($parts as $part) {
		    if (($part = base64_decode($part, true)) === false) {
			    throw new InvalidArgumentException('Ciphertext is invalid');
		    }

		    $plaintext .= $Key->decrypt($part);
	    }

        return $plaintext;
    }

    /**
     * Шифрование данных по алгоритму RSA
     * с добавлением сессионного токена в качестве "соли".
     *
     * @param   string  $plaintext  Исходные данные
     * @return  string
     */
    public static function encryptBySession(string $plaintext): string
    {
        $saltyData = [
            'salt' => Tokenizer::getSessionToken(),
            'data' => base64_encode($plaintext)
        ];

        return self::encrypt( toJson($saltyData) );
    }

    /**
     * Дешифрование данных по алгоритму RSA с проверкой
     * сессионного токена, использованного в качестве "соли".
     *
     * @param   string  $ciphertext  Зашифрованные данные
     * @return  string
     */
    public static function decryptBySession(string $ciphertext): string
    {
        $saltyData = fromJson( self::decrypt($ciphertext) );

        if (
            is_string($saltyData['data'] ?? null)
            && Tokenizer::verifySessionToken($saltyData['salt'] ?? '')
        ) {
            return base64_decode($saltyData['data']);
        }

        throw new InvalidArgumentException('Ciphertext is invalid');
    }

    /**
     * Проверка надёжности пароля:
     * - минимальная длина — self::PASSWORD_MIN_LEN символов;
     * - пароль не может состоять только из цифр;
     * - пароль не должен быть похож на логин или email.
     *
     * @param   string  $password
     * @param   string  $login
     * @param   string  $email
     * @return  bool    Пароль надёжный?
     */
    public static function isStrongPassword(string $password, string $login, string $email): bool
    {
        return strLength($password) >= self::PASSWORD_MIN_LEN
            && !preg_match('/^\d+$/', $password)
            && stripos($password, $login) === false
            && stripos($password, $email) === false;
    }

    /**
     * Вычисление хэша пароля с использованием случайной соли.
     * Применяется алгоритм Blowfish.
     *
     * Чем выше значение $cost, тем больше времени требуется на вычисление хэша
     * и проверку пароля. Более высокие значения замедляют brute force атаки.
     *
     * The time taken to compute the hash doubles for every increment by one of $cost.
     * For example, if the hash takes 1 second to compute when $cost
     * is 14 then then the compute time varies as 2^($cost - 14) seconds.
     *
     * @param   string  $password  Пароль для хэширования
     * @param   int     $cost      Весовой параметр в диапазоне 4..31 для алгоритма Blowfish
     * @return  string
     */
    public static function calculatePasswordHash(string $password, int $cost = 0): string
    {
        if ($cost < 4 || $cost > 31) {
            $cost = self::PASSWORD_HASH_COST;
        }

        return password_hash($password, PASSWORD_BCRYPT, ['cost' => $cost]);
    }

    /**
     * Проверка пароля по хэшу.
     *
     * @param   string  $password  Пароль для проверки
     * @param   string  $hash      Хэш правильного пароля
     * @return  bool    Пароль оказался верным?
     */
    public static function validatePassword(string $password, string $hash): bool
    {
        if ($password === '') {
            throw new InvalidArgumentException('Password cannot be empty');
        }

        if (
            !preg_match('/^\$2[axy]\$(\d\d)\$[.\/0-9A-Za-z]{22}/', $hash, $matches)
            || $matches[1] < 4
            || $matches[1] > 31
        ) {
            return false;
        }

        return password_verify($password, $hash);
    }

    /**
     * Устойчивое к атаке по времени сравнение двух строк.
     * @link  http://blog.astrumfutura.com/2010/10/nanosecond-scale-remote-timing-attacks-on-php-applications-time-to-take-them-seriously/
     * @link  http://blog.ircmaxell.com/2012/12/seven-ways-to-screw-up-bcrypt.html
     *
     * @param   string  $expected  Строка, с которой выполняется сравнение
     * @param   string  $actual    Строка, предоставленная пользователем
     * @return  bool    Совпадают ли строки
     */
    public static function compareStrings(string $expected, string $actual): bool
    {
        // Prevent issues if string length is 0
        $expected .= "\0";
        $actual .= "\0";

        $expectedLength = mb_strlen($expected, '8bit');
        $actualLength = mb_strlen($actual, '8bit');

        // Set the result to the difference between the lengths
        $diff = $expectedLength - $actualLength;

        for ($i = 0; $i < $actualLength; $i++) {
            $diff |= (ord($actual[$i]) ^ ord($expected[$i % $expectedLength]));
        }

        return $diff === 0;
    }
}
