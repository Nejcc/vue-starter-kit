<?php

declare(strict_types=1);

namespace Nejcc\Subscribe\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Nejcc\Subscribe\DTOs\SubscriberList as SubscriberListDTO;
use Nejcc\Subscribe\Facades\Subscribe;
use Nejcc\Subscribe\Models\SubscriptionList;

final class ListController extends Controller
{
    public function index(): Response
    {
        $lists = SubscriptionList::withCount(['subscribers', 'activeSubscribers'])
            ->orderBy('name')
            ->paginate(25);

        return Inertia::render('admin/subscribers/lists/Index', [
            'lists' => $lists,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_public' => ['boolean'],
            'is_default' => ['boolean'],
            'double_opt_in' => ['boolean'],
            'welcome_email_enabled' => ['boolean'],
            'welcome_email_subject' => ['nullable', 'required_if:welcome_email_enabled,true', 'string', 'max:255'],
            'welcome_email_content' => ['nullable', 'required_if:welcome_email_enabled,true', 'string'],
        ]);

        if ($validated['is_default'] ?? false) {
            SubscriptionList::where('is_default', true)->update(['is_default' => false]);
        }

        $list = SubscriptionList::create([
            ...$validated,
            'slug' => Str::slug($validated['name']),
        ]);

        if (config('subscribe.sync.enabled', true)) {
            $dto = SubscriberListDTO::fromArray([
                'name' => $list->name,
                'description' => $list->description,
                'is_public' => $list->is_public,
                'double_opt_in' => $list->double_opt_in,
            ]);

            $result = Subscribe::createList($dto);

            if ($result->success && $result->providerId) {
                $list->update(['provider_id' => $result->providerId]);
            }
        }

        return redirect()->route('admin.subscribers.lists.index')
            ->with('success', 'List created successfully.');
    }

    public function show(SubscriptionList $list): Response
    {
        $list->loadCount(['subscribers', 'activeSubscribers']);

        $subscribers = $list->subscribers()
            ->latest()
            ->paginate(25);

        return Inertia::render('admin/subscribers/lists/Show', [
            'list' => $list,
            'subscribers' => $subscribers,
        ]);
    }

    public function update(Request $request, SubscriptionList $list): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_public' => ['boolean'],
            'is_default' => ['boolean'],
            'double_opt_in' => ['boolean'],
            'welcome_email_enabled' => ['boolean'],
            'welcome_email_subject' => ['nullable', 'required_if:welcome_email_enabled,true', 'string', 'max:255'],
            'welcome_email_content' => ['nullable', 'required_if:welcome_email_enabled,true', 'string'],
        ]);

        if (($validated['is_default'] ?? false) && !$list->is_default) {
            SubscriptionList::where('is_default', true)->update(['is_default' => false]);
        }

        $list->update([
            ...$validated,
            'slug' => Str::slug($validated['name']),
        ]);

        return back()->with('success', 'List updated successfully.');
    }

    public function destroy(SubscriptionList $list): RedirectResponse
    {
        if ($list->is_default) {
            return back()->with('error', 'Cannot delete the default list.');
        }

        $list->subscribers()->detach();
        $list->delete();

        return redirect()->route('admin.subscribers.lists.index')
            ->with('success', 'List deleted successfully.');
    }
}
