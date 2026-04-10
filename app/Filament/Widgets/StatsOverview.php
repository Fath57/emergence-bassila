<?php

namespace App\Filament\Widgets;

use App\Models\BlogPost;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total membres', User::count())
                ->description('Utilisateurs inscrits')
                ->icon('heroicon-o-users')
                ->color('primary'),

            Stat::make('Inscriptions ce mois', User::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count())
                ->description('Nouveaux membres')
                ->icon('heroicon-o-user-plus')
                ->color('success'),

            Stat::make('Articles publiés ce mois', BlogPost::published()->whereMonth('published_at', now()->month)->whereYear('published_at', now()->year)->count())
                ->description('Ce mois-ci')
                ->icon('heroicon-o-document-text')
                ->color('info'),
        ];
    }
}
