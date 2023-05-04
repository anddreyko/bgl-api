<?php

namespace App\Core\Http\ValueObjects;

/**
 * Вспомогательный класс сборки полного URL-адреса и анализа его составных частей.
 *
 * @see \Tests\Unit\Core\Http\ValueObjects\UrlTest
 */
class Url
{
    /** @var string|null Протокол. */
    public ?string $scheme = null;
    /** @var string|null Домен. */
    public ?string $host = null;
    /** @var int|null Порт. */
    public ?int $port = null;
    /** @var string|null Имя пользователя. */
    public ?string $user = null;
    /** @var string|null Пароль пользователя. */
    public ?string $pass = null;
    /** @var string|null Относительный путь. */
    public ?string $path = null;
    /** @var string|null Строка GET-параметров. */
    public ?string $query = null;
    /** @var string|null Якорь. */
    public ?string $fragment = null;

    /**
     * Получение объекта URL из его строки.
     *
     * @param string $url
     *
     * @return self
     * @throws \InvalidArgumentException
     */
    public static function convertFromString(string $url): self
    {
        /**
         * @var array{
         *     scheme?: string,
         *     host?: string,
         *     port?: int,
         *     user?: string,
         *     pass?: string,
         *     query?: mixed,
         *     path?: string,
         *     fragment?: string
         * } $parsedUrl
         */
        $parsedUrl = parse_url($url);

        return self::convertFromArray($parsedUrl);
    }

    /**
     * Получение объекта URL из ассоциативного массива, индексы которого соответствуют публичным свойствам этого класса.
     *
     * @param array{
     *     scheme?: string,
     *     host?: string,
     *     port?: int,
     *     user?: string,
     *     pass?: string,
     *     query?: mixed,
     *     path?: string,
     *     fragment?: string
     * } $url
     *
     * @return self
     */
    public static function convertFromArray(array $url): self
    {
        return (new self())->convert($url);
    }

    /**
     * Получение объекта URL из ассоциативного массива, индексы которого соответствуют публичным свойствам этого класса.
     *
     * @param array{
     *     scheme?: string,
     *     host?: string,
     *     port?: int,
     *     user?: string,
     *     pass?: string,
     *     query?: mixed,
     *     path?: string,
     *     fragment?: string
     * } $params
     *
     * @return self
     */
    public function convert(array $params): self
    {
        $url = new self();

        /**
         * @var string $key
         * @var mixed $value
         */
        foreach ($params as $key => $value) {
            if (!property_exists(self::class, $key)) {
                continue;
            }
            if ($key === 'query' && is_array($value)) {
                $value = http_build_query($value);
            }

            $url->$key = $value;
        }

        return $url;
    }

    /**
     * Получение полной строки ссылки.
     *
     * @param bool $protocolWithoutScheme
     *
     * @return string
     */
    public function getUrl(bool $protocolWithoutScheme = false): string
    {
        return
            implode(
                '',
                [
                    'scheme' => $this->scheme,
                    'protocol' => isset($this->scheme) ? ':' : '',
                    'slashes_protocol' => isset($this->scheme) || $protocolWithoutScheme ? '//' : '',
                    'user' => $this->user,
                    'colon_pass' => isset($this->pass) ? ':' : '',
                    'pass' => isset($this->user) ? $this->pass : '',
                    'at' => isset($this->user) ? '@' : '',
                    'host' => $this->host,
                    'colon_port' => isset($this->port) ? ':' : '',
                    'port' => $this->port,
                ]
            ) . $this->getRelativeUrl();
    }

    /**
     * Получение относительного пути.
     *
     * @return string
     */
    public function getRelativeUrl(): string
    {
        return implode(
            '',
            [
                'slash_path' => isset($this->path) && (isset($this->host) || !isset($this->scheme)) ? '/' : '',
                'path' => isset($this->path) ? ltrim($this->path, '/') : '',
                'question' => isset($this->query) ? '?' : '',
                'query' => $this->query,
                'anchor' => isset($this->fragment) ? '#' : '',
                'fragment' => $this->fragment,
            ]
        );
    }

    /**
     * Получение GET-параметра по ключу.
     *
     * @param string $key
     *
     * @return mixed|null
     */
    public function getQueryParam(string $key): mixed
    {
        $parsedQuery = $this->getParsedQuery();

        return $parsedQuery[$key] ?? null;
    }

    /**
     * Получение GET-параметров запроса.
     *
     * @return array<array-key, mixed>
     */
    public function getParsedQuery(): array
    {
        if (!empty($this->query)) {
            parse_str($this->query, $parsedQuery);
        } else {
            $parsedQuery = [];
        }

        return $parsedQuery;
    }
}
