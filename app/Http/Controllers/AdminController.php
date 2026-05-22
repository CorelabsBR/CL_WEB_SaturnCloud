<?php

namespace App\Http\Controllers;

use App\Models\CategoriaProd;
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
            ],
            'products' => Product::with(['categoria', 'jogo'])
                ->orderByRaw('jogo_id is null')
                ->orderBy('jogo_id')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(),
            'categories' => CategoriaProd::orderBy('nome')->get(),
            'games' => JogoProd::orderBy('nome')->get(),
            'tickets' => SupportTicket::with(['user', 'messages.user'])
                ->latest('atualizado_em')
                ->get(),
            'orders' => $this->vpsOrdersQuery()
                ->with(['user', 'payments', 'renewalService.product'])
                ->latest('criado_em')
                ->limit(24)
                ->get(),
        ]);
    }

    public function storeProduct(Request $request)
    {
        $this->ensureAdmin();

        $data = $this->validateProduct($request);
        $data['imagem'] = $this->upload($request, 'imagem', 'uploads/products');
        $data['ddos_protection'] = $request->boolean('ddos_protection');
        $data['featured'] = $request->boolean('featured');

        Product::create($data);

        return back()->with('admin_success', 'Produto cadastrado com sucesso.');
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

        return back()->with('admin_success', 'Produto atualizado com sucesso.');
    }

    public function destroyProduct(Product $product)
    {
        $this->ensureAdmin();

        $product->delete();

        return back()->with('admin_success', 'Produto removido com sucesso.');
    }

    public function updateGame(Request $request, JogoProd $game)
    {
        $this->ensureAdmin();

        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:jogos_prod,slug,' . $game->id],
            'banner' => ['nullable', 'image', 'max:4096'],
            'icon' => ['nullable', 'image', 'max:2048'],
            'ativo' => ['nullable', 'boolean'],
        ]);

        $data['ativo'] = $request->boolean('ativo');

        if ($banner = $this->upload($request, 'banner', 'img/games', true)) {
            $data['banner'] = $banner;
        }

        if ($icon = $this->upload($request, 'icon', 'img/games', true)) {
            $data['icon'] = $icon;
        }

        $game->update($data);

        return back()->with('admin_success', 'Jogo atualizado com sucesso.');
    }

    public function replyTicket(Request $request, SupportTicket $ticket)
    {
        $this->ensureAdmin();

        $data = $request->validate([
            'message' => ['required', 'string', 'min:2', 'max:2000'],
            'status' => ['required', 'in:aberto,respondido,fechado'],
        ]);

        $ticket->messages()->create([
            'user_id' => Auth::id(),
            'author_type' => 'admin',
            'body' => $data['message'],
        ]);

        $ticket->update(['status' => $data['status']]);

        return back()->with('admin_success', 'Resposta enviada ao cliente.');
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

        return response()->json([
            'signature' => $this->supportSignature(),
        ]);
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
        $name = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $filename = $name . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path($directory), $filename);

        return $basenameOnly ? $filename : $directory . '/' . $filename;
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
