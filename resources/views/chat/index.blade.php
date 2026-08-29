@extends('layouts.app')

@section('title', 'AI Food & Dining Assistant - ' . \App\Models\RestaurantSetting::get('name', 'Restaurant'))

@section('content')
<div style="max-width: 900px; margin: 0 auto;">
    <div style="margin-bottom: 20px;">
        <h1 style="font-size: 22px; font-weight: 800;"><i class="bi bi-robot"></i> AI Food & Dining Assistant</h1>
        <p style="font-size: 13px; color: var(--text-muted);">Ask questions about our menu, vegetarian dishes, spice levels, table availability, or active orders.</p>
    </div>

    <div class="grid" style="grid-template-columns: 260px 1fr; gap: 20px; align-items: start;">
        <!-- Left: Conversations Sidebar -->
        <div class="card" style="padding: 14px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <strong style="font-size: 14px;">Conversations</strong>
                <form method="POST" action="{{ route('chat.create') }}" style="margin: 0;">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-sm" title="New Chat"><i class="bi bi-plus-lg"></i> New</button>
                </form>
            </div>

            @if($conversations->isEmpty())
                <p style="color: var(--text-muted); font-size: 12px;">No chat history yet.</p>
            @else
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    @foreach($conversations as $conv)
                        <a href="{{ route('chat.index', ['conversation_id' => $conv->id]) }}" 
                           style="padding: 8px 10px; border-radius: var(--radius); font-size: 13px; text-decoration: none; display: block; {{ $currentConversation && $currentConversation->id === $conv->id ? 'background: var(--primary-light); color: var(--primary); font-weight: bold;' : 'color: var(--text-main); background: var(--bg-page);' }}">
                            <i class="bi bi-chat-dots"></i> {{ Str::limit($conv->title, 22) }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Right: Chat Box -->
        <div class="card" style="padding: 16px; display: flex; flex-direction: column; min-height: 480px;">
            <div style="flex: 1; overflow-y: auto; max-height: 400px; display: flex; flex-direction: column; gap: 14px; margin-bottom: 16px; padding-right: 6px;">
                @if(!$currentConversation || $currentConversation->messages->isEmpty())
                    <div style="text-align: center; padding: 40px 10px; color: var(--text-muted);">
                        <i class="bi bi-chat-quote" style="font-size: 40px; display: block; margin-bottom: 10px; color: var(--primary);"></i>
                        <h3 style="font-size: 16px; font-weight: bold; color: var(--text-main); margin-bottom: 6px;">How can I help with your meal today?</h3>
                        <p style="font-size: 13px; max-width: 400px; margin: 0 auto 16px;">Try asking questions like:</p>
                        <div style="display: flex; flex-wrap: wrap; gap: 8px; justify-content: center; font-size: 12px;">
                            <span style="background: var(--bg-page); border: 1px solid var(--border); padding: 4px 10px; border-radius: 14px;">"What food is vegetarian?"</span>
                            <span style="background: var(--bg-page); border: 1px solid var(--border); padding: 4px 10px; border-radius: 14px;">"What are today's chef specials?"</span>
                            <span style="background: var(--bg-page); border: 1px solid var(--border); padding: 4px 10px; border-radius: 14px;">"Which dishes are spicy?"</span>
                            <span style="background: var(--bg-page); border: 1px solid var(--border); padding: 4px 10px; border-radius: 14px;">"What is my order status?"</span>
                        </div>
                    </div>
                @else
                    @foreach($currentConversation->messages as $msg)
                        <div style="display: flex; gap: 10px; {{ $msg->role === 'user' ? 'justify-content: flex-end;' : 'justify-content: flex-start;' }}">
                            @if($msg->role !== 'user')
                                <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--primary-light); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0;">
                                    <i class="bi bi-robot"></i>
                                </div>
                            @endif

                            <div style="max-width: 75%; padding: 10px 14px; border-radius: var(--radius); font-size: 13px; line-height: 1.5; {{ $msg->role === 'user' ? 'background: var(--primary); color: #ffffff;' : 'background: var(--bg-page); border: 1px solid var(--border); color: var(--text-main);' }}">
                                {!! nl2br(e($msg->content)) !!}
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            <!-- Message Input Form -->
            @if($currentConversation)
                <form method="POST" action="{{ route('chat.send', $currentConversation->id) }}" style="display: flex; gap: 8px; border-top: 1px solid var(--border); padding-top: 12px; margin-top: auto;">
                    @csrf
                    <input type="text" name="message" class="form-control" placeholder="Ask about menu, dietary options, orders..." required autofocus>
                    <button type="submit" class="btn btn-primary" style="white-space: nowrap;"><i class="bi bi-send"></i> Send</button>
                </form>
            @else
                <form method="POST" action="{{ route('chat.create') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary" style="width: 100%;"><i class="bi bi-plus-circle"></i> Start New Chat</button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
