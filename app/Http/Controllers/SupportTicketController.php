<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            'message' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $ticket = null;

        if ($data['subject'] === 'Atendimento pelo chat') {
            $ticket = SupportTicket::where('user_id', Auth::id())
                ->where('subject', 'Atendimento pelo chat')
                ->whereIn('status', ['aberto', 'respondido'])
                ->latest('atualizado_em')
                ->first();
        }

        if (! $ticket) {
            $ticket = SupportTicket::create([
                'user_id' => Auth::id(),
                'subject' => $data['subject'],
                'category' => $data['category'],
                'priority' => $data['priority'],
                'status' => 'aberto',
            ]);
        }

        $ticket->messages()->create([
            'user_id' => Auth::id(),
            'author_type' => 'cliente',
            'body' => $data['message'],
        ]);

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
            ->with('success', 'Mensagem enviada com sucesso. Nossa equipe já pode acompanhar sua solicitação.');
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
                            'time' => optional($message->criado_em)->format('d/m/Y H:i'),
                        ];
                    });
                })
                ->values(),
        ];
    }
}
