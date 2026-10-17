<?php

namespace App\Services;

use App\Models\MarketingCampaign;
use App\Models\MarketingContent;
use App\Models\MarketingEvent;
use App\Models\MarketingIntegration;
use App\Models\Order;
use App\Models\User;

class MarketingDashboardService
{
    public function data(): array
    {
        $integrations = collect(['instagram', 'facebook', 'linkedin', 'discord', 'telegram', 'google_search_console'])
            ->map(fn ($provider) => MarketingIntegration::firstOrCreate(['provider' => $provider], ['enabled' => false]));

        return [
            'marketingCampaigns' => MarketingCampaign::with('product')->latest()->limit(50)->get(),
            'marketingContents' => MarketingContent::with('author')->withCount('revisions')->latest()->limit(50)->get(),
            'marketingIntegrations' => $integrations,
            'marketingMetrics' => [
                'campaigns' => MarketingCampaign::count(),
                'pending' => MarketingCampaign::where('status', 'pending_approval')->count() + MarketingContent::where('status', 'pending_approval')->count(),
                'published' => MarketingContent::where('status', 'published')->count(),
                'events' => MarketingEvent::where('created_at', '>=', now()->subDays(30))->count(),
                'customers' => User::where('tipo_usuario', 'cliente')->count(),
                'paid_orders' => Order::where('status', 'pago')->count(),
                'paid_revenue' => (float) Order::where('status', 'pago')->sum('total'),
            ],
        ];
    }
}
