<?php
/**
 * Статичный класс Randomizer.
 *
 * Методы для генерации случайных (или почти случайных) данных.
 *
 * @author Dmitry Lunin
 */
class Randomizer
{
	/** Длина генерируемых паролей по умолчанию */
    public const PASSWORD_DEFAULT_LEN = 16;

	/** Длина генерируемых строк по умолчанию */
    public const DEFAULT_LEN = 32;

	/**
	 * Генерация криптографически безопасных псевдослучайных байт.
	 *
	 * @param   int  $length  Количество генерируемых байт
	 * @return  string
	 */
	public static function getBytes(int $length = self::DEFAULT_LEN): string
	{
		if ($length < 1) {
			throw new InvalidArgumentException('Argument $length must be an integer and greater than 0');
		}

		return random_bytes($length);
	}

	/**
	 * Генерация случайной строки типа base64 ([A-Za-z0-9_-]+).
	 *
	 * @param   int  $length  Требуемая длина строки
	 * @return  string
	 */
	public static function getString(int $length = self::DEFAULT_LEN): string
	{
		$bytes = self::getBytes($length);

		return strtr(substr(base64_encode($bytes), 0, $length), '+/', '_-');
	}

	/**
	 * Генерация шестнадцатеричной случайной строки.
	 *
	 * @param   int  $length  Требуемая длина строки
	 * @return  string
	 */
	public static function getHex(int $length = self::DEFAULT_LEN): string
	{
		$bytes = self::getBytes($length / 2 + 1);

		return substr(bin2hex($bytes), 0, $length);
	}

	/**
	 * Генерация пароля.
	 *
	 * @param   int  $length  Длина пароля
	 * @return  string
	 */
	public static function getPassword(int $length = self::PASSWORD_DEFAULT_LEN): string
	{
		$a = ['e','y','u','i','o','a'];
		$b = ['q','w','r','t','p','s','d','f','g','h','j','k','l','z','x','c','v','b','n','m'];
		$c = ['1','2','3','4','5','6','7','8','9','0'];
		$e = ['-','_','!','~','$','*','@',':','|'];

		$password = $b[ array_rand($b) ];

		for ($size = 0; $size < $length; $size++) {
			$lastChar = $password[ $size - 1 ];
			$prevLastChar = $size > 1 ? $password[ $size - 2 ] : '';
			if (in_array($lastChar, $b)) { //последняя буква была согласной
				if (in_array($prevLastChar, $a)) { // две последние буквы были согласными
					$r = random_int(0, 2);
					if ($r) {
						$password .= $a[ array_rand($a) ];
					} else {
						$password .= $b[ array_rand($b) ];
					}
				} else {
					$password .= $a[ array_rand($a) ];
				}
			} else {
				$r = random_int(0, 2);
				if ($r === 2) {
					$password .= $b[ array_rand($b) ];
				} elseif ($r === 1) {
					$password .= $e[ array_rand($e)] ;
				} else {
					$password .= $c[ array_rand($c) ];
				}
			}
		}

		return $password;
	}

	/**
	 * Генерация псевдослучайного пароля, удобного для запоминания.
	 *
	 * @param   int  $length    Длина пароля
	 * @param   int  $strength  Сложность пароля (1..8)
	 * @return  string
	 */
	public static function getPasswordSmart(
		int $length = self::PASSWORD_DEFAULT_LEN,
		int $strength = 4
	): string {
		$length = max($length, Security::PASSWORD_MIN_LEN);

		$vowels = 'aeuy';
		$consonants = 'bdghjmnpqrstvz';
		if ($strength >= 1) {
			$consonants .= 'BDGHJLMNPQRSTVWXZ';
		}
		if ($strength >= 2) {
			$vowels .= 'AEUY';
		}
		if ($strength >= 4) {
			$consonants .= '23456789';
		}
		if ($strength >= 8) {
			$vowels .= '@#$%';
		}

		$password = '';
		$alt = $_SERVER['REQUEST_TIME'] % 2;
		for ($i = 0; $i < $length; $i++) {
			if ($alt === 1) {
				$password .= $consonants[ mt_rand() % strlen($consonants) ];
				$alt = 0;
			} else {
				$password .= $vowels[ mt_rand() % strlen($vowels) ];
				$alt = 1;
			}
		}

		return $password;
	}
}
