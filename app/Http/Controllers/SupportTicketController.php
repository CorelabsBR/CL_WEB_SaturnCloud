<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SupportTicketController extends Controller
{
    public function index()
    {
        $tickets = SupportTicket::with('messages.user')
            ->where('user_id', Auth::id())
            ->latest('atualizado_em')
            ->get();

        return view('suporte.index', compact('tickets'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'min:4', 'max:120'],
            'category' => ['required', 'string', 'max:60'],
            'priority' => ['required', 'in:baixa,normal,alta,urgente'],
            'message' => ['nullable', 'required_without:image', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'max:8192'],
        ]);

        $ticket = null;

        if ($data['subject'] === 'Atendimento pelo chat') {
            $ticket = SupportTicket::where('user_id', Auth::id())
                ->where('subject', 'Atendimento pelo chat')
                ->whereIn('status', ['aberto', 'respondido'])
                ->latest('atualizado_em')
                ->first();
        }

        $isNewTicket = false;

        if (! $ticket) {
            $ticket = SupportTicket::create([
                'user_id' => Auth::id(),
                'subject' => $data['subject'],
                'category' => $data['category'],
                'priority' => $data['priority'],
                'status' => 'aberto',
            ]);
            $isNewTicket = true;
        }

        $ticket->messages()->create([
            'user_id' => Auth::id(),
            'author_type' => 'cliente',
            'body' => $data['message'] ?? '',
            'image_path' => $this->uploadSupportImage($request),
        ]);

        if ($isNewTicket && $data['subject'] === 'Atendimento pelo chat') {
            $ticket->messages()->create([
                'user_id' => null,
                'author_type' => 'admin',
                'body' => 'Esperando o responsável.',
            ]);
        }

        $ticket->update([
            'status' => 'aberto',
            'priority' => $data['priority'],
        ]);

        if ($request->expectsJson()) {
            return response()->json($this->messagesPayload());
        }

        return redirect()
            ->back()
            ->with('support_widget_open', true)
            ->with('success', 'Esperando o responsável.');
    }

    public function messages()
    {
        return response()->json($this->messagesPayload());
    }

    private function messagesPayload(): array
    {
        $tickets = SupportTicket::with('messages.user')
            ->where('user_id', Auth::id())
            ->latest('atualizado_em')
            ->limit(5)
            ->get();

        return [
            'open_count' => $tickets->whereIn('status', ['aberto', 'respondido'])->count(),
            'messages' => $tickets
                ->flatMap(function (SupportTicket $ticket) {
                    return $ticket->messages->map(function ($message) use ($ticket) {
                        return [
                            'ticket_id' => $ticket->id,
                            'author_type' => $message->author_type,
                            'author_name' => $message->author_type === 'admin'
                                ? ($message->user?->nome ?? 'Suporte')
                                : 'Você',
                            'body' => $message->body,
                            'image_url' => $message->image_path ? asset($message->image_path) : null,
                            'time' => optional($message->criado_em)->format('d/m/Y H:i'),
                        ];
                    });
                })
                ->values(),
        ];
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
}
