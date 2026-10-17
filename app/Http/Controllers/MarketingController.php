<?php

namespace App\Http\Controllers;

use App\Models\MarketingAuditLog;
use App\Models\MarketingCampaign;
use App\Models\MarketingContent;
use App\Models\MarketingContentRevision;
use App\Models\MarketingEvent;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MarketingController extends Controller
{
    public function index()
    {
        return redirect()->to(route('admin.index') . '#marketing');
    }

    public function storeCampaign(Request $request)
    {
        $data = $this->campaignData($request);
        $data['created_by'] = $request->user()->id;
        $campaign = MarketingCampaign::create($data);
        $this->audit($request, 'campaign.created', $campaign, $campaign->toArray());
        return $this->respond($request, 'Campanha criada como rascunho.', $campaign, 201);
    }

    public function updateCampaign(Request $request, MarketingCampaign $campaign)
    {
        $before = $campaign->toArray();
        $data = $this->campaignData($request, true);
        if (($data['status'] ?? null) === 'active' && ! $campaign->approved_at) {
            return back()->withErrors(['status' => 'A campanha precisa ser aprovada antes de ser ativada.']);
        }
        if (($data['status'] ?? null) === 'scheduled' && ! $campaign->approved_at) {
            return back()->withErrors(['status' => 'A campanha precisa ser aprovada antes de ser agendada.']);
        }
        $campaign->update($data);
        $this->audit($request, 'campaign.updated', $campaign, ['before' => $before, 'after' => $campaign->fresh()->toArray()]);
        return $this->respond($request, 'Campanha atualizada.', $campaign->fresh());
    }

    public function approveCampaign(Request $request, MarketingCampaign $campaign)
    {
        $campaign->update(['status' => 'draft', 'approved_by' => $request->user()->id, 'approved_at' => now()]);
        $this->audit($request, 'campaign.approved', $campaign);
        return $this->respond($request, 'Campanha aprovada e mantida como rascunho. Nenhuma ação externa foi realizada.', $campaign->fresh());
    }

    public function storeContent(Request $request)
    {
        $data = $this->contentData($request);
        $data['author_id'] = $request->user()->id;
        $data['slug'] = $data['slug'] ?: Str::slug($data['title']);
        $content = DB::transaction(function () use ($data, $request) {
            $content = MarketingContent::create($data);
            MarketingContentRevision::create(['content_id' => $content->id, 'body_markdown' => $content->body_markdown, 'created_by' => $request->user()->id]);
            return $content;
        });
        $this->audit($request, 'content.created', $content);
        return $this->respond($request, 'Conteúdo salvo como rascunho.', $content, 201);
    }

    public function updateContent(Request $request, MarketingContent $content)
    {
        $data = $this->contentData($request, $content);
        if (in_array($data['status'] ?? null, ['scheduled', 'published'], true) && ! $content->approved_by) {
            return back()->withErrors(['status' => 'O conteúdo precisa de aprovação antes do agendamento ou publicação.']);
        }
        DB::transaction(function () use ($data, $content, $request) {
            if (isset($data['body_markdown']) && $data['body_markdown'] !== $content->body_markdown) {
                MarketingContentRevision::create(['content_id' => $content->id, 'body_markdown' => $data['body_markdown'], 'created_by' => $request->user()->id]);
            }
            $content->update($data);
        });
        $this->audit($request, 'content.updated', $content);
        return $this->respond($request, 'Conteúdo atualizado.', $content->fresh());
    }

    public function approveContent(Request $request, MarketingContent $content)
    {
        $content->update(['status' => 'draft', 'approved_by' => $request->user()->id]);
        $this->audit($request, 'content.approved', $content);
        return $this->respond($request, 'Conteúdo aprovado e mantido como rascunho. A publicação continua manual.', $content->fresh());
    }

    public function track(Request $request)
    {
        $data = $request->validate([
            'consent' => ['accepted'], 'event' => ['required', Rule::in(['page_view', 'cta_click', 'signup_started'])],
            'path' => ['required', 'string', 'max:2048'], 'utm_source' => ['nullable', 'string', 'max:100'],
            'utm_medium' => ['nullable', 'string', 'max:100'], 'utm_campaign' => ['nullable', 'string', 'max:150'],
        ]);
        $host = parse_url((string) $request->headers->get('referer'), PHP_URL_HOST);
        MarketingEvent::create([
            'event' => $data['event'], 'session_hash' => hash('sha256', $request->session()->getId()),
            'path' => $data['path'], 'referrer_host' => $host ? Str::limit($host, 255, '') : null,
            'utm_source' => $data['utm_source'] ?? null, 'utm_medium' => $data['utm_medium'] ?? null,
            'utm_campaign' => $data['utm_campaign'] ?? null, 'user_id' => $request->user()?->id, 'created_at' => now(),
        ]);
        return response()->json(['recorded' => true], 201);
    }

    private function campaignData(Request $request, bool $partial = false): array
    {
        $sometimes = $partial ? 'sometimes' : 'required';
        return $request->validate([
            'name' => [$sometimes, 'string', 'max:255'], 'objective' => [$sometimes, 'string', 'max:255'],
            'product_id' => ['nullable', 'exists:products,id'], 'audience' => [$sometimes, 'string', 'max:500'],
            'channel' => [$sometimes, Rule::in(['organic', 'email', 'instagram', 'facebook', 'linkedin', 'discord', 'telegram', 'other'])],
            'message' => [$sometimes, 'string', 'max:5000'], 'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'], 'budget_limit' => ['nullable', 'numeric', 'min:0'],
            'tracking_url' => ['nullable', 'url:http,https', 'max:2048'], 'status' => ['nullable', Rule::in(MarketingCampaign::STATUSES)],
        ]);
    }

    private function contentData(Request $request, ?MarketingContent $content = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'], 'slug' => ['nullable', 'alpha_dash', 'max:255', Rule::unique('marketing_contents', 'slug')->ignore($content?->id)],
            'type' => ['required', Rule::in(['article', 'news', 'tutorial', 'announcement'])], 'body_markdown' => ['required', 'string', 'max:100000'],
            'excerpt' => ['nullable', 'string', 'max:500'], 'category' => ['nullable', 'string', 'max:100'],
            'seo_title' => ['nullable', 'string', 'max:60'], 'seo_description' => ['nullable', 'string', 'max:160'],
            'status' => ['nullable', Rule::in(MarketingContent::STATUSES)], 'scheduled_at' => ['nullable', 'date', 'after:now'],
        ]);
    }

    private function audit(Request $request, string $action, $subject, array $changes = []): void
    {
        MarketingAuditLog::create(['user_id' => $request->user()?->id, 'action' => $action, 'subject_type' => get_class($subject),
            'subject_id' => $subject->id, 'changes' => $changes ?: null, 'ip_hash' => hash('sha256', (string) $request->ip()), 'created_at' => now()]);
    }

    private function respond(Request $request, string $message, $resource, int $status = 200)
    {
        return $request->expectsJson()
            ? response()->json(['message' => $message, 'data' => $resource], $status)
            : redirect()->to(route('admin.index') . '#marketing')->with('marketing_success', $message);
    }
}
