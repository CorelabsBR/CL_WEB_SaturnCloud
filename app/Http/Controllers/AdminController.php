<?php

namespace App\Http\Controllers;

use App\Models\CategoriaProd;
use App\Models\Coupon;
use App\Models\JogoProd;
use App\Models\Order;
use App\Models\Product;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function index()
    {
        $this->ensureAdmin();

        return view('admin.index', [
            'stats' => [
                'users' => User::where('tipo_usuario', 'cliente')->count(),
                'products' => Product::count(),
                'openTickets' => SupportTicket::whereIn('status', ['aberto', 'respondido'])->count(),
                'orders' => $this->vpsOrdersQuery()->count(),
                'coupons' => Coupon::count(),
            ],
            'products' => Product::with(['categoria', 'jogo'])
                ->orderByRaw('jogo_id is null')
                ->orderBy('jogo_id')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(),
            'categories' => CategoriaProd::orderBy('nome')->get(),
            'games' => JogoProd::orderBy('nome')->get(),
            'gameFontOptions' => JogoProd::FONT_OPTIONS,
            'users' => User::orderByRaw("tipo_usuario = 'root' desc")
                ->orderBy('nome')
                ->get(),
            'tickets' => SupportTicket::with(['user', 'messages.user'])
                ->latest('atualizado_em')
                ->get(),
            'orders' => $this->vpsOrdersQuery()
                ->with(['user', 'payments', 'renewalService.product'])
                ->latest('criado_em')
                ->limit(24)
                ->get(),
            'coupons' => Coupon::latest('created_at')->get(),
        ]);
    }

    public function storeCoupon(Request $request)
    {
        $this->ensureAdmin();

        Coupon::create($this->validateCoupon($request));

        return $this->adminMutationResponse($request, 'Cupom criado com sucesso.');
    }

    public function destroyCoupon(Coupon $coupon)
    {
        $this->ensureAdmin();

        $coupon->delete();

        return $this->adminMutationResponse(request(), 'Cupom removido com sucesso.');
    }

    public function storeProduct(Request $request)
    {
        $this->ensureAdmin();

        $data = $this->validateProduct($request);
        $data['imagem'] = $this->upload($request, 'imagem', 'uploads/products');
        $data['ddos_protection'] = $request->boolean('ddos_protection');
        $data['featured'] = $request->boolean('featured');

        Product::create($data);

        return $this->adminMutationResponse($request, 'Produto cadastrado com sucesso.');
    }

    public function updateProduct(Request $request, Product $product)
    {
        $this->ensureAdmin();

        $data = $this->validateProduct($request);
        $data['ddos_protection'] = $request->boolean('ddos_protection');
        $data['featured'] = $request->boolean('featured');

        if ($path = $this->upload($request, 'imagem', 'uploads/products')) {
            $data['imagem'] = $path;
        }

        $product->update($data);

        return $this->adminMutationResponse($request, 'Produto atualizado com sucesso.');
    }

    public function destroyProduct(Product $product)
    {
        $this->ensureAdmin();

        $product->delete();

        return $this->adminMutationResponse(request(), 'Produto removido com sucesso.');
    }

    public function updateGame(Request $request, JogoProd $game)
    {
        $this->ensureAdmin();

        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:jogos_prod,slug,' . $game->id],
            'font_family' => ['nullable', 'string', 'in:default,minecraft,terraria,gta'],
            'banner' => ['nullable', 'image', 'max:4096'],
            'hero_banner' => ['nullable', 'image', 'max:4096'],
            'icon' => ['nullable', 'image', 'max:2048'],
            'ativo' => ['nullable', 'boolean'],
            'ativo_admin' => ['nullable', 'boolean'],
        ]);

        $data['ativo'] = $request->boolean('ativo');
        $data['ativo_admin'] = $request->boolean('ativo_admin');
        $data['font_family'] = $data['font_family'] === 'default' ? null : $data['font_family'];

        if ($banner = $this->upload($request, 'banner', 'img/games', true)) {
            $data['banner'] = $banner;
        }

        if ($heroBanner = $this->upload($request, 'hero_banner', 'img/games', true)) {
            $data['hero_banner'] = $heroBanner;
        }

        if ($icon = $this->upload($request, 'icon', 'img/games/icons', true)) {
            $data['icon'] = $icon;
        }

        $game->update($data);

        return $this->adminMutationResponse($request, 'Jogo atualizado com sucesso.');
    }

    public function timeoutUser(Request $request, User $user)
    {
        $this->ensureAdmin();
        abort_if($user->tipo_usuario === 'root', 403);

        $data = $request->validate([
            'duration_mode' => ['required', 'in:preset,custom,unlimited'],
            'duration_minutes' => ['nullable', 'required_if:duration_mode,preset', 'integer', 'min:1', 'max:43200'],
            'custom_duration_minutes' => ['nullable', 'required_if:duration_mode,custom', 'integer', 'min:1', 'max:525600'],
            'timeout_reason' => ['nullable', 'string', 'max:255'],
        ]);

        $timeoutUntil = $data['duration_mode'] === 'unlimited'
            ? now()->addYears(100)
            : now()->addMinutes((int) ($data['duration_mode'] === 'custom'
                ? $data['custom_duration_minutes']
                : $data['duration_minutes']));

        $user->update([
            'timeout_until' => $timeoutUntil,
            'timeout_reason' => $data['timeout_reason'] ?? null,
        ]);

        return $this->adminMutationResponse($request, 'Timeout aplicado ao usuario.');
    }

    public function clearUserTimeout(User $user)
    {
        $this->ensureAdmin();

        $user->update([
            'timeout_until' => null,
            'timeout_reason' => null,
        ]);

        return $this->adminMutationResponse(request(), 'Timeout removido do usuario.');
    }

    public function promoteUserToRoot(Request $request, User $user)
    {
        $this->ensureAdmin();

        if ($user->tipo_usuario === 'root') {
            return $this->adminMutationResponse($request, 'Usuario ja e root.');
        }

        $user->update([
            'tipo_usuario' => 'root',
            'timeout_until' => null,
            'timeout_reason' => null,
        ]);

        return $this->adminMutationResponse($request, 'Usuario promovido para root.');
    }

    public function demoteRootUser(Request $request, User $user)
    {
        $this->ensureAdmin();
        abort_if((int) $user->id === (int) Auth::id(), 403);
        abort_if($user->email === 'root@vortex.local', 403);

        if ($user->tipo_usuario !== 'root') {
            return $this->adminMutationResponse($request, 'Usuario ja e cliente.');
        }

        $user->update([
            'tipo_usuario' => 'cliente',
        ]);

        return $this->adminMutationResponse($request, 'Root removido do usuario.');
    }

    public function replyTicket(Request $request, SupportTicket $ticket)
    {
        $this->ensureAdmin();

        $data = $request->validate([
            'message' => ['nullable', 'required_without:image', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'max:8192'],
        ]);

        $ticket->messages()->create([
            'user_id' => Auth::id(),
            'author_type' => 'admin',
            'body' => $data['message'] ?? '',
            'image_path' => $this->uploadSupportImage($request),
        ]);

        $ticket->update(['status' => 'respondido']);

        if ($request->expectsJson()) {
            return response()->json($this->supportPayload());
        }

        return back()->with('admin_success', 'Resposta enviada ao cliente.');
    }

    public function destroyTicket(SupportTicket $ticket)
    {
        $this->ensureAdmin();

        $ticket->delete();

        if (request()->expectsJson()) {
            return response()->json($this->supportPayload());
        }

        return back()->with('admin_success', 'Chat fechado.');
    }

    public function updateTicket(Request $request, SupportTicket $ticket)
    {
        $this->ensureAdmin();

        $data = $request->validate([
            'status' => ['required', 'in:aberto,respondido,fechado'],
            'priority' => ['required', 'in:baixa,normal,alta,urgente'],
        ]);

        $ticket->update($data);

        return back()->with('admin_success', 'Chamado atualizado.');
    }

    public function supportPulse()
    {
        $this->ensureAdmin();

        return response()->json($this->supportPayload());
    }

    private function adminMutationResponse(Request $request, string $message)
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'message' => $message,
            ]);
        }

        return back()->with('admin_success', $message);
    }

    private function supportPayload(): array
    {
        $tickets = SupportTicket::with('messages.user', 'user')
            ->latest('atualizado_em')
            ->get();

        return [
            'signature' => $this->supportSignature(),
            'tickets' => $tickets->map(function ($ticket) {
                $lastMessage = $ticket->messages->last();
                $clientName = $ticket->user?->nome ?? 'Cliente';

                return [
                    'id' => $ticket->id,
                    'client_name' => $clientName,
                    'initial' => Str::upper(Str::substr($clientName, 0, 1)),
                    'subject' => $ticket->subject,
                    'category' => $ticket->category,
                    'preview' => $lastMessage?->body
                        ? Str::limit($lastMessage->body, 44)
                        : ($lastMessage?->image_path ? 'Imagem enviada' : $ticket->subject),
                    'last_time' => optional($lastMessage?->criado_em)->format('H:i'),
                    'reply_url' => route('admin.tickets.reply', $ticket),
                    'destroy_url' => route('admin.tickets.destroy', $ticket),
                    'messages' => $ticket->messages->map(function ($message) use ($ticket) {
                        return [
                            'id' => $message->id,
                            'author_type' => $message->author_type,
                            'author_name' => $message->author_type === 'admin'
                                ? ($message->user?->nome ?? 'Admin')
                                : ($ticket->user?->nome ?? 'Cliente'),
                            'body' => $message->body,
                            'image_url' => $message->image_path ? asset($message->image_path) : null,
                            'time' => optional($message->criado_em)->format('d/m/Y H:i'),
                        ];
                    })->toArray(),
                ];
            })->toArray(),
        ];
    }

    private function validateProduct(Request $request): array
    {
        return $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string', 'max:2000'],
            'preco' => ['required', 'numeric', 'min:0'],
            'categoria_id' => ['required', 'exists:categoria_prod,id'],
            'jogo_id' => ['nullable', 'exists:jogos_prod,id'],
            'plan' => ['nullable', 'integer', 'min:1'],
            'local_id' => ['nullable', 'integer', 'min:1'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'ram' => ['nullable', 'string', 'max:80'],
            'armazenamento' => ['nullable', 'string', 'max:80'],
            'processamento' => ['nullable', 'string', 'max:80'],
            'bancos_dados' => ['nullable', 'string', 'max:80'],
            'dominios' => ['nullable', 'string', 'max:80'],
            'imagem' => ['nullable', 'image', 'max:4096'],
        ]);
    }

    private function validateCoupon(Request $request, ?Coupon $coupon = null): array
    {
        $couponId = $coupon?->id ?? 'NULL';

        $data = $request->validate([
            'code' => ['required', 'string', 'max:60', 'unique:coupons,code,' . $couponId],
            'type' => ['required', 'in:percent,fixed'],
            'value' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'active' => ['nullable', 'boolean'],
            'single_use' => ['nullable', 'boolean'],
        ]);

        $data['code'] = trim($data['code']);
        $data['active'] = $request->boolean('active');
        $data['usage_limit'] = $request->boolean('single_use') ? 1 : null;
        unset($data['single_use']);

        if ($data['type'] === 'percent') {
            $data['value'] = min(100, (float) $data['value']);
        }

        return $data;
    }

    private function vpsOrdersQuery()
    {
        return Order::query()
            ->where(function ($query) {
                $query
                    ->whereHas('items.product.categoria', fn ($category) => $category->where('nome', 'like', '%VPS%'))
                    ->orWhereHas('renewalService.product.categoria', fn ($category) => $category->where('nome', 'like', '%VPS%'));
            });
    }

    private function upload(Request $request, string $field, string $directory, bool $basenameOnly = false): ?string
    {
        if (! $request->hasFile($field)) {
            return null;
        }

        $file = $request->file($field);
        if (! is_dir(public_path($directory))) {
            mkdir(public_path($directory), 0755, true);
        }

        $name = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $filename = $name . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path($directory), $filename);

        return $basenameOnly ? $filename : $directory . '/' . $filename;
    }

    private function uploadSupportImage(Request $request): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        $directory = public_path('uploads/support');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $file = $request->file('image');
        $name = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'imagem';
        $filename = $name . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move($directory, $filename);

        return 'uploads/support/' . $filename;
    }

    private function ensureAdmin(): void
    {
        abort_unless(Auth::check() && Auth::user()->tipo_usuario === 'root', 403);
    }

    private function supportSignature(): string
    {
        return implode(':', [
            SupportTicket::count(),
            SupportTicket::max('atualizado_em'),
            SupportMessage::count(),
            SupportMessage::max('criado_em'),
        ]);
    }
}
