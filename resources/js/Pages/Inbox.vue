<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import MarketplaceLayout from '@/Layouts/MarketplaceLayout.vue';
import { eur } from '@/lib/format.js';
import { t } from '@/lib/i18n.js';

const props = defineProps({
    conversations: { type: Array, default: () => [] },
    active: { type: Object, default: null },
});

const totalUnread = computed(() =>
    props.conversations.reduce((sum, c) => sum + (c.unread || 0), 0),
);

const form = useForm({ body: '' });

const send = () => {
    if (!props.active || !form.body.trim()) return;
    form.post(route('conversations.message', props.active.id), {
        preserveScroll: true,
        onSuccess: () => form.reset('body'),
    });
};
</script>

<template>
    <Head :title="t('nav.inbox', 'Inbox')" />
    <MarketplaceLayout>
        <div class="wrap inbox-wrap">
            <div class="inbox panel">
                <!-- Conversation list -->
                <aside class="inbox-list">
                    <div class="list-head">
                        <h4>{{ t('nav.messages', 'Messages') }}</h4>
                        <span v-if="totalUnread" class="tag tag-accent">{{ totalUnread }} {{ t('inbox.unread', 'unread') }}</span>
                    </div>

                    <div v-if="conversations.length" class="conv-scroll">
                        <Link
                            v-for="c in conversations"
                            :key="c.id"
                            :href="route('inbox', { conversation: c.id })"
                            preserve-scroll
                            :class="['conv', { active: active && active.id === c.id }]"
                        >
                            <div class="conv-top">
                                <span class="conv-with">{{ c.with }}</span>
                                <span class="conv-time mono">{{ c.last_at }}</span>
                            </div>
                            <div class="conv-veh mono mono-accent">{{ c.vehicle.title }}</div>
                            <div class="conv-bottom">
                                <span class="conv-last text-muted">{{ c.last }}</span>
                                <span v-if="c.unread > 0" class="badge-dot">{{ c.unread }}</span>
                            </div>
                        </Link>
                    </div>
                    <div v-else class="list-empty text-muted">{{ t('inbox.empty', 'No conversations yet.') }}</div>
                </aside>

                <!-- Thread -->
                <section class="inbox-thread">
                    <template v-if="active">
                        <header class="thread-head">
                            <div class="thread-who">
                                <span class="thread-avatar">{{ active.with.charAt(0) }}</span>
                                <div class="thread-meta">
                                    <div class="thread-name">{{ active.with }}</div>
                                    <div class="mono">{{ t('inbox.about', 'About') }} {{ active.vehicle.title }} · {{ eur(active.vehicle.price) }}</div>
                                </div>
                            </div>
                            <Link :href="route('vehicles.show', active.vehicle.slug)" class="btn btn-secondary btn-sm">{{ t('inbox.view_listing', 'View listing') }}</Link>
                        </header>

                        <div class="thread-body">
                            <div v-for="m in active.messages" :key="m.id" :class="['msg-row', m.mine ? 'mine' : 'theirs']">
                                <div class="msg">
                                    <div class="msg-bubble">{{ m.body }}</div>
                                    <div class="msg-time mono">{{ m.at }}</div>
                                </div>
                            </div>
                        </div>

                        <form class="thread-reply" @submit.prevent="send">
                            <input
                                v-model="form.body"
                                class="input"
                                type="text"
                                :placeholder="t('inbox.placeholder', 'Write a message…')"
                                :disabled="form.processing"
                            />
                            <button type="submit" class="btn btn-primary" :disabled="form.processing || !form.body.trim()">{{ t('inbox.send', 'Send') }}</button>
                        </form>
                    </template>

                    <div v-else class="thread-empty">
                        <div class="mono mono-accent">{{ t('nav.inbox', 'Inbox') }}</div>
                        <h3>{{ t('inbox.select_title', 'Select a conversation') }}</h3>
                        <p class="text-muted">{{ t('inbox.select_text', 'Pick a thread on the left to read and reply to buyer messages.') }}</p>
                    </div>
                </section>
            </div>
        </div>
    </MarketplaceLayout>
</template>

<style scoped>
.inbox-wrap { padding: 40px 32px; }
.inbox { display: flex; height: min(72vh, 760px); min-height: 520px; overflow: hidden; }

/* Conversation list */
.inbox-list { width: 320px; flex: none; border-right: 1px solid var(--color-divider); display: flex; flex-direction: column; min-height: 0; }
.list-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 16px 18px; border-bottom: 1px solid var(--color-divider); }
.list-head h4 { margin: 0; }
.conv-scroll { flex: 1; overflow-y: auto; min-height: 0; padding: 10px; display: flex; flex-direction: column; gap: 6px; }
.conv { position: relative; display: block; padding: 12px 14px; border-radius: var(--radius-md); border: 1px solid transparent; color: var(--color-text); transition: background .15s var(--ease), border-color .15s var(--ease); }
.conv:hover { background: var(--color-surface); color: var(--color-text); }
.conv.active { background: var(--color-accent-100); border-color: var(--color-accent-200); color: var(--color-text); }
.conv.active::before { content: ""; position: absolute; left: 6px; top: 13px; bottom: 13px; width: 3px; border-radius: var(--radius-pill); background: var(--color-accent); }
.conv-top { display: flex; align-items: baseline; justify-content: space-between; gap: 10px; }
.conv-with { font: 800 14px var(--font-heading); }
.conv-time { flex: none; color: var(--color-neutral-600); }
.conv-veh { margin: 6px 0; }
.conv-bottom { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
.conv-last { font-size: 13px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; min-width: 0; }
.list-empty { padding: 24px 18px; font-size: 13px; }

/* Thread */
.inbox-thread { flex: 1; display: flex; flex-direction: column; min-width: 0; min-height: 0; }
.thread-head { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 14px 20px; border-bottom: 1px solid var(--color-divider); }
.thread-who { display: flex; align-items: center; gap: 12px; min-width: 0; }
.thread-avatar { width: 42px; height: 42px; flex: none; display: grid; place-items: center; background: var(--color-accent); color: #fff; font: 800 17px var(--font-heading); border-radius: var(--radius-pill); box-shadow: var(--shadow-xs); }
.thread-meta { min-width: 0; }
.thread-name { font: 800 16px var(--font-heading); }
.thread-meta .mono { margin-top: 5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.thread-body { flex: 1; overflow-y: auto; min-height: 0; padding: 20px; display: flex; flex-direction: column; gap: 14px; }
.msg-row { display: flex; }
.msg-row.mine { justify-content: flex-end; }
.msg-row.theirs { justify-content: flex-start; }
.msg { max-width: 74%; display: flex; flex-direction: column; }
.msg-row.mine .msg { align-items: flex-end; }
.msg-bubble { padding: 10px 14px; font-size: 14px; line-height: 1.45; border-radius: var(--radius-lg); }
.msg-row.theirs .msg-bubble { background: var(--color-surface); color: var(--color-text); border: 1px solid var(--color-hairline); border-bottom-left-radius: var(--radius-sm); }
.msg-row.mine .msg-bubble { background: var(--color-accent); color: #fff; border-bottom-right-radius: var(--radius-sm); box-shadow: var(--shadow-xs); }
.msg-time { margin-top: 5px; color: var(--color-neutral-600); }
.msg-row.mine .msg-time { text-align: right; }

.thread-reply { display: flex; gap: 10px; padding: 14px 18px; border-top: 1px solid var(--color-divider); }
.thread-reply .input { flex: 1; }

.thread-empty { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; gap: 8px; padding: 40px; }
.thread-empty h3 { margin: 6px 0 0; }
.thread-empty p { max-width: 34ch; }

@media (max-width: 820px) {
    .inbox-wrap { padding: 24px 14px; }
    .inbox { flex-direction: column; height: auto; min-height: 0; }
    .inbox-list { width: 100%; border-right: 0; border-bottom: 1px solid var(--color-divider); max-height: 300px; }
    .inbox-thread { min-height: 62vh; }
    .thread-body { max-height: 52vh; }
}
</style>
