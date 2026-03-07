<?php

declare(strict_types=1);

namespace Bgl\Tests\Support\Modules;

use Codeception\Module;
use Codeception\Module\Db;
use Codeception\Module\REST;

final class AuthModule extends Module
{
    /**
     * Register a new user, activate in DB, sign in, and set Bearer header.
     *
     * @return array{access_token: string, refresh_token: string, user_id: string}
     */
    public function registerAndLogin(string $email, string $password): array
    {
        $rest = $this->getRest();

        $rest->sendPost('/v1/auth/sign-up', [
            'email' => $email,
            'password' => $password,
        ]);
        $rest->seeResponseCodeIs(201);

        $this->getDb()->updateInDatabase(
            'auth_user',
            ['status' => 'active'],
            ['email' => $email],
        );

        $rest->sendPost('/v1/auth/sign-in', [
            'email' => $email,
            'password' => $password,
        ]);
        $rest->seeResponseCodeIs(200);

        /** @var string $responseBody */
        $responseBody = $rest->grabResponse();

        /** @var array{data: array{access_token: string, refresh_token: string}} $decoded */
        $decoded = json_decode($responseBody, true, 512, JSON_THROW_ON_ERROR);

        $accessToken = $decoded['data']['access_token'];
        $refreshToken = $decoded['data']['refresh_token'];

        $userId = $this->extractUserIdFromDb($email);

        $rest->haveHttpHeader('Authorization', 'Bearer ' . $accessToken);

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'user_id' => $userId,
        ];
    }

    private function extractUserIdFromDb(string $email): string
    {
        /** @var string $userId */
        $userId = $this->getDb()->grabFromDatabase('auth_user', 'id', ['email' => $email]);

        return $userId;
    }

    private function getRest(): REST
    {
        /** @var REST $rest */
        $rest = $this->getModule('REST');

        return $rest;
    }

    private function getDb(): Db
    {
        /** @var Db $db */
        $db = $this->getModule('Db');

        return $db;
    }
}
