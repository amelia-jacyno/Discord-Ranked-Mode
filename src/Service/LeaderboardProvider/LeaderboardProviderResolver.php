<?php

namespace App\Service\LeaderboardProvider;

class LeaderboardProviderResolver
{
    protected static array $providers = [
        Mee6LeaderboardProvider::class,
        AmeBotLeaderboardProvider::class,
        ArcaneLeaderboardProvider::class
    ];

    /**
     * @return class-string<LeaderboardProvider>
     *
     * @throws \Exception
     */
    public static function resolveProvider(string $provider): string
    {
        foreach (static::$providers as $providerClass) {
            if ($providerClass::getProviderName() === $provider) {
                return $providerClass;
            }
        }

        throw new \Exception('Provider not found');
    }
}