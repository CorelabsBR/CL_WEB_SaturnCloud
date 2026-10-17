<?php

namespace Tests\Feature;

use App\Models\User;
use App\Http\Middleware\EnsureMarketingAdmin;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class MarketingAccessTest extends TestCase
{
    public function test_marketing_panel_requires_authentication(): void
    {
        $this->get('/admin/marketing')->assertRedirect('/login');
    }

    public function test_non_root_user_cannot_access_marketing_panel(): void
    {
        $user = new User(['tipo_usuario' => 'cliente']);
        $user->id = 999999;
        $request = Request::create('/admin/marketing');
        $request->setUserResolver(fn () => $user);

        $this->expectException(HttpException::class);
        (new EnsureMarketingAdmin())->handle($request, fn () => response('ok'));
    }

    public function test_analytics_rejects_events_without_explicit_consent(): void
    {
        $this->postJson('/marketing/events', [
            'consent' => false,
            'event' => 'page_view',
            'path' => '/',
        ])->assertUnprocessable()->assertJsonValidationErrors('consent');
    }
}
