<?php
/**
 * Статичный класс Tokenizer.
 *
 * Набор методов для работы с проверочными токенами.
 *
 * @author Dmitriy Lunin
 */
class Tokenizer
{
	/**
	 * Генерация и сохранение сессионного токена,
	 * если это ещё не было сделано.
	 *
	 * @param  bool  $force  Пересоздать токен
	 */
	public static function makeSessionToken(bool $force = false): void
	{
		if (!isset($_SESSION['SECURITY_TOKEN']) || $force) {
			$_SESSION['SECURITY_TOKEN'] = Randomizer::getString();
		}
	}

	/**
	 * Получение текущего сессионного токена.
	 *
	 * @return string
	 */
	public static function getSessionToken(): string
	{
		self::makeSessionToken();
		return $_SESSION['SECURITY_TOKEN'];
	}

	/**
	 * Проверка сессионного токена, призванная препятствовать CSRF-атакам.
	 *
	 * @param   string  $token  Проверяемое значение
	 * @return  bool
	 */
	public static function verifySessionToken(string $token): bool
	{
		return $token
			&& isset($_SESSION['SECURITY_TOKEN'])
			&& strcmp($token, $_SESSION['SECURITY_TOKEN']) === 0;
	}

	/**
	 * Кодирование данных для JWT.
	 *
	 * @param   array  $data
	 * @return  string
	 */
	protected static function encodeJwtData(array $data): string
	{
		return base64UrlEncode( toJson($data) );
	}

	/**
	 * Декодирование данных для JWT.
	 *
	 * @param   string  $dataEnc
	 * @return  array
	 */
	protected static function decodeJwtData(string $dataEnc): array
	{
		return fromJson( base64_decode($dataEnc) );
	}

	/**
	 * Вычисление подписи данных для JWT.
	 *
	 * @param   string  $str  Подписываемая строка
	 * @return  string
	 */
	protected static function createJwtSign(string $str): string
	{
		// 32 == strlen("-----BEGIN RSA PRIVATE KEY-----\n")
		return base64UrlEncode(
			hex2bin(
				hash_hmac('sha256', $str, substr(Cfg::RSA_PRIVATE_KEY, 32))
			)
		);
	}

	/**
	 * Создание JWT.
	 *
	 * @param   array  $payloadRaw  Полезная нагрузка токена
	 * @param   int    $lifetime    Время жизни токена (сек.)
	 * @return  string
	 */
	public static function createJwt(array $payloadRaw, int $lifetime): string
	{
		$headerRaw = [
			'typ' => 'JWT',
			'alg' => 'HS256'
		];
		$header = self::encodeJwtData($headerRaw);

		$payloadRaw['exp'] = Time::toStamp() + $lifetime;
		$payload = self::encodeJwtData($payloadRaw);

		$data = "{$header}.{$payload}";

		$sign = self::createJwtSign($data);

		return "{$data}.{$sign}";
	}

	/**
	 * Проверка подписи JWT.
	 *
	 * @param   string  $header   Закодированный заголовок
	 * @param   string  $payload  Закодированная полезная нагрузка
	 * @param   string  $sign     Подпись
	 * @return  bool
	 */
	public static function validateJwtSign(string $header, string $payload, string $sign): bool
	{
		$signRef = self::createJwtSign("{$header}.{$payload}");

		return hash_equals($signRef, $sign);
	}

	/**
	 * Проверка действительности JWT и извлечение из него полезной нагрузки.
	 *
	 * @param   string  $token  Токен
	 * @return  array
	 */
	public static function getJwtPayload(string $token): array
	{
		$tokenParts = explode('.', $token);
		if (!isset($tokenParts[2])) {
			throw new TokenizerEx( TokenizerEx::TOKEN_IS_INVALID );
		}

        [$header, $payload, $sign] = $tokenParts;

		if (!self::validateJwtSign($header, $payload, $sign)) {
			throw new TokenizerEx( TokenizerEx::SIGN_IS_INVALID );
		}

		$payload = self::decodeJwtData($payload);

		if (
			($payload['exp'] ?? 0) <= Time::toStamp()
		) {
			throw new TokenizerEx( TokenizerEx::TOKEN_HAS_EXPIRED );
		}

		return $payload;
	}
}
