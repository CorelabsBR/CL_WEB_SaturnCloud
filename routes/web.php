<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WebHostingController;
use App\Http\Controllers\GameHostingController;
use App\Http\Controllers\CartCheckoutController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HestiaPanelController;
use App\Http\Controllers\MercadoPagoWebhookController;
use App\Http\Controllers\PterodactylServerConfigurationController;
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\PteroPanelController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\MarketingPublicController;
use App\Http\Controllers\VpsController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PublicPageController;
use App\Http\Controllers\HelpCenterController;

Route::get('/', HomeController::class)->name('home');
Route::get('/ajuda', [HelpCenterController::class, 'index'])->name('help.index');
Route::get('/ajuda/{slug}', [HelpCenterController::class, 'show'])->name('help.show');
Route::get('/{page}', [PublicPageController::class, 'show'])
    ->whereIn('page', ['revenda', 'dominios', 'infraestrutura', 'sobre', 'faq', 'contato', 'sla'])
    ->name('pages.show');
Route::get('/conteudos', [MarketingPublicController::class, 'index'])->name('content.index');
Route::get('/conteudos/{content:slug}', [MarketingPublicController::class, 'show'])->name('content.show');
Route::get('/sitemap.xml', [MarketingPublicController::class, 'sitemap'])->name('sitemap');
Route::get('/vps', [VpsController::class, 'index'])->name('vps.index');
Route::get('/vps/linux', fn () => app(PublicPageController::class)->show('vps-linux'))->name('vps.linux');
Route::get('/vps/windows', fn () => app(PublicPageController::class)->show('vps-windows'))->name('vps.windows');
Route::get('/termos', fn () => view('termos.service'))->name('termos.service');
Route::get('/politica-privacidade', fn () => view('privacidade.index'))->name('privacidade');
Route::get('/reembolso', fn () => view('reembolso.index'))->name('reembolso');
Route::get('/404', fn () => abort(404));
Route::get('/403', fn () => abort(403));
Route::get('/500', fn () => abort(500));

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

Route::middleware('guest')->group(function () {
    Route::get('/registro', [AuthController::class, 'showRegister'])->name('registro');
    Route::post('/registro', [AuthController::class, 'register'])->name('registro.post');
});

Route::get('/completar-cadastro', [AuthController::class, 'showCompleteProfile'])
    ->name('cadastro.completar');

Route::post('/completar-cadastro', [AuthController::class, 'completeProfile'])
    ->name('cadastro.completar.post');

Route::get('/verificar-email', [AuthController::class, 'showEmailVerification'])
    ->name('cadastro.verificar.email');

Route::post('/verificar-email', [AuthController::class, 'verifyEmail'])
    ->name('cadastro.verificar.email.post');

Route::post('/verificar-email/reenviar', [AuthController::class, 'resendEmailVerificationCode'])
    ->name('cadastro.verificar.email.reenviar');

Route::middleware('auth')->group(function () {
    Route::get('/perfil', [AuthController::class, 'showEditProfile'])
        ->name('perfil.edit');

    Route::patch('/perfil', [AuthController::class, 'updateProfile'])
        ->name('perfil.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/recuperar-senha', [AuthController::class, 'showForgotPassword'])
    ->name('password.request');

Route::post('/recuperar-senha', [AuthController::class, 'sendPasswordResetCode'])
    ->name('password.email');

Route::get('/recuperar-senha/codigo', [AuthController::class, 'showPasswordResetCode'])
    ->name('password.code');

Route::post('/recuperar-senha/codigo', [AuthController::class, 'verifyPasswordResetCode'])
    ->name('password.code.verify');

Route::get('/recuperar-senha/nova-senha', [AuthController::class, 'showResetPasswordForm'])
    ->name('password.reset.form');

Route::post('/recuperar-senha/nova-senha', [AuthController::class, 'resetPassword'])
    ->name('password.update');

Route::get('/web', [WebHostingController::class, 'index'])->name('web');

##Route::get('/games', [GameHostingController::class, 'index'])->name('games.index');
##Route::get('/games/{jogo:slug}', [GameHostingController::class, 'show'])->name('games.show');

Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])
    ->name('social.redirect');

Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])
    ->name('social.callback');

Route::middleware(['auth', 'profile.complete'])->group(function () {
    Route::get('/carrinho', [CartController::class, 'index'])->name('cart.index');
    Route::post('/carrinho/add-plan/{plan}', [CartController::class, 'addPlan'])->name('cart.addPlan');
    Route::post('/carrinho/produto/{product}', [CartController::class, 'addProduct'])->name('cart.addProduct');
    Route::patch('/carrinho/update/{cartItem}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/carrinho/remove/{cartItem}', [CartController::class, 'remove'])->name('cart.remove');
    Route::delete('/carrinho/clear', [CartController::class, 'clear'])->name('cart.clear');
});

Route::middleware('auth')->group(function () {
    Route::get('/suporte', [SupportTicketController::class, 'index'])->name('ticket.index');
    Route::get('/suporte/mensagens', [SupportTicketController::class, 'messages'])->name('ticket.messages');

    Route::post('/suporte', [SupportTicketController::class, 'store'])->name('ticket.store');

});

Route::middleware(['auth', 'profile.complete'])->group(function () {
    Route::get('/painel', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('/painel/renovacoes/processar', [DashboardController::class, 'processRenewals'])
        ->name('dashboard.renewals.process');
    Route::post('/servicos/{service}/cancelar', [DashboardController::class, 'cancelService'])
        ->name('services.cancel');
    Route::get('/dashboard', fn () => redirect()->route('dashboard.index'));
});

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::post('/produtos', [AdminController::class, 'storeProduct'])->name('products.store');
    Route::patch('/produtos/{product}', [AdminController::class, 'updateProduct'])->name('products.update');
    Route::delete('/produtos/{product}', [AdminController::class, 'destroyProduct'])->name('products.destroy');
    Route::post('/cupons', [AdminController::class, 'storeCoupon'])->name('coupons.store');
    Route::delete('/cupons/{coupon}', [AdminController::class, 'destroyCoupon'])->name('coupons.destroy');
    Route::patch('/jogos/{game}', [AdminController::class, 'updateGame'])->name('games.update');
    Route::patch('/usuarios/{user}/timeout', [AdminController::class, 'timeoutUser'])->name('users.timeout');
    Route::delete('/usuarios/{user}/timeout', [AdminController::class, 'clearUserTimeout'])->name('users.timeout.clear');
    Route::patch('/usuarios/{user}/root', [AdminController::class, 'promoteUserToRoot'])->name('users.root.promote');
    Route::delete('/usuarios/{user}/root', [AdminController::class, 'demoteRootUser'])->name('users.root.demote');
    Route::get('/suporte/pulse', [AdminController::class, 'supportPulse'])->name('tickets.pulse');
    Route::patch('/suporte/{ticket}', [AdminController::class, 'updateTicket'])->name('tickets.update');
    Route::post('/suporte/{ticket}/responder', [AdminController::class, 'replyTicket'])->name('tickets.reply');
    Route::delete('/suporte/{ticket}', [AdminController::class, 'destroyTicket'])->name('tickets.destroy');
});

Route::middleware(['auth', 'profile.complete'])->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/cupom', [CheckoutController::class, 'applyCoupon'])->name('checkout.coupon.apply');
    Route::delete('/checkout/cupom', [CheckoutController::class, 'removeCoupon'])->name('checkout.coupon.remove');
    Route::post('/checkout/pagar', [CheckoutController::class, 'pay'])->name('checkout.pay');
    Route::post('/checkout/pedido/{order}/pagar', [CheckoutController::class, 'payOrder'])->name('checkout.order.pay');
    Route::post('/checkout/pedido/{order}/pix', [CheckoutController::class, 'pix'])->name('checkout.order.pix');
    Route::get('/checkout/pedido/{order}/status', [CheckoutController::class, 'orderStatus'])->name('checkout.order.status');
    Route::get('/servicos/{service}/configurar-pterodactyl', [PterodactylServerConfigurationController::class, 'edit'])
        ->name('pterodactyl.configure.edit');
    Route::patch('/servicos/{service}/configurar-pterodactyl', [PterodactylServerConfigurationController::class, 'update'])
        ->name('pterodactyl.configure.update');
    Route::get('/servicos/{service}/painel-hestia', HestiaPanelController::class)
        ->name('hestia.panel');
    Route::get('/servicos/{service}/painel-pterodactyl', PteroPanelController::class)
        ->name('ptero.panel');

    Route::get('/checkout/sucesso', [CheckoutController::class, 'success'])->name('checkout.success');
    Route::get('/checkout/pendente', [CheckoutController::class, 'pending'])->name('checkout.pending');
    Route::get('/checkout/falhou', [CheckoutController::class, 'failure'])->name('checkout.failure');

});

Route::post('/webhooks/mercadopago', [MercadoPagoWebhookController::class, 'handle'])
    ->name('webhooks.mercadopago')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

Route::post('/marketing/events', [MarketingController::class, 'track'])
    ->middleware('throttle:60,1')
    ->name('marketing.events');

Route::middleware(['auth', 'marketing.admin', 'throttle:120,1'])
    ->prefix('admin/marketing')
    ->name('marketing.')
    ->group(function () {
        Route::get('/', [MarketingController::class, 'index'])->name('index');
        Route::post('/campaigns', [MarketingController::class, 'storeCampaign'])->name('campaigns.store');
        Route::patch('/campaigns/{campaign}', [MarketingController::class, 'updateCampaign'])->name('campaigns.update');
        Route::post('/campaigns/{campaign}/approve', [MarketingController::class, 'approveCampaign'])->name('campaigns.approve');
        Route::post('/contents', [MarketingController::class, 'storeContent'])->name('contents.store');
        Route::patch('/contents/{content}', [MarketingController::class, 'updateContent'])->name('contents.update');
        Route::post('/contents/{content}/approve', [MarketingController::class, 'approveContent'])->name('contents.approve');
        Route::get('/api/campaigns', fn () => \App\Models\MarketingCampaign::with('product')->latest()->paginate(50))->name('api.campaigns');
        Route::get('/api/contents', fn () => \App\Models\MarketingContent::latest()->paginate(50))->name('api.contents');
        Route::get('/openapi.yaml', fn () => response()->file(base_path('docs/marketing-openapi.yaml')))->name('openapi');
    });
