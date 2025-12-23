// Text-only chat widget (voice features removed)
(function () {
  const defaultApi = 'http://127.0.0.1:5055/ask';
  const api = window.AI_COM_API || defaultApi;
  const STORAGE_KEY = window.AI_COM_STORAGE_KEY || 'AI_COM_CHAT_HISTORY';
  let chatHistory = [];

  const storage = (() => {
    try {
      const testKey = '__ai_com_storage_test__';
      window.localStorage.setItem(testKey, 'ok');
      window.localStorage.removeItem(testKey);
      return window.localStorage;
    } catch (err) {
      console.warn('localStorage unavailable; chat history will reset on refresh.', err);
      return null;
    }
  })();

  function createChatWidget() {
    const root = document.createElement('div');
    Object.assign(root, { id: 'ai-chat-widget' });
    Object.assign(root.style, {
      position: 'fixed',
      right: '16px',
      bottom: '16px',
      width: '320px',
      maxHeight: '60vh',
      background: '#fff',
      border: '1px solid #e5e7eb',
      borderRadius: '10px',
      boxShadow: '0 10px 30px rgba(0,0,0,0.12)',
      display: 'flex',
      flexDirection: 'column',
      overflow: 'hidden',
      zIndex: '9999'
    });

    const header = document.createElement('div');
    Object.assign(header.style, {
      background: '#2563eb',
      color: '#fff',
      padding: '10px 12px',
      fontWeight: 'bold',
      display: 'flex',
      justifyContent: 'space-between',
      alignItems: 'center'
    });

    const headerTitle = document.createElement('span');
    headerTitle.textContent = 'Shopping Assistant';
    header.appendChild(headerTitle);

    const clearBtn = document.createElement('button');
    Object.assign(clearBtn, { type: 'button', textContent: 'Clear', title: 'Clear chat messages' });
    Object.assign(clearBtn.style, {
      background: 'transparent',
      border: '1px solid #fff',
      color: '#fff',
      cursor: 'pointer',
      fontSize: '12px',
      padding: '4px 8px',
      borderRadius: '6px'
    });
    header.appendChild(clearBtn);

    const messages = document.createElement('div');
    Object.assign(messages.style, {
      flex: '1',
      padding: '10px',
      overflowY: 'auto',
      fontSize: '14px',
      lineHeight: '1.4'
    });

    const form = document.createElement('form');
    Object.assign(form.style, {
      display: 'flex',
      gap: '6px',
      padding: '8px',
      borderTop: '1px solid #e5e7eb',
      alignItems: 'center'
    });

    const input = document.createElement('input');
    Object.assign(input, { type: 'text', placeholder: 'Ask about products or your cart...' });
    Object.assign(input.style, {
      flex: '1',
      padding: '8px',
      border: '1px solid #e5e7eb',
      borderRadius: '6px'
    });

    const send = document.createElement('button');
    Object.assign(send, { type: 'submit', textContent: 'Send' });
    Object.assign(send.style, {
      background: '#2563eb',
      color: '#fff',
      border: 'none',
      borderRadius: '6px',
      padding: '8px 12px'
    });

    form.appendChild(input);
    form.appendChild(send);

    root.appendChild(header);
    root.appendChild(messages);
    root.appendChild(form);

    function loadHistory() {
      if (!storage) return;
      try {
        const raw = storage.getItem(STORAGE_KEY);
        if (!raw) return;
        const parsed = JSON.parse(raw);
        if (Array.isArray(parsed)) {
          chatHistory = parsed.slice(-200);
          chatHistory.forEach(msg => append(msg.role, msg.text, false));
        }
      } catch (err) {
        console.warn('Failed to load chat history', err);
      }
    }

    function persist(role, text) {
      chatHistory.push({ role, text });
      chatHistory = chatHistory.slice(-200);
      if (!storage) return;
      try {
        storage.setItem(STORAGE_KEY, JSON.stringify(chatHistory));
      } catch (err) {
        console.warn('Failed to persist chat history', err);
      }
    }

    function clearHistory(showConfirmation = true) {
      chatHistory = [];
      messages.innerHTML = '';
      if (storage) {
        try {
          storage.removeItem(STORAGE_KEY);
        } catch (err) {
          console.warn('Failed to clear chat history', err);
        }
      }
      if (showConfirmation) {
        append('system', 'Chat cleared.', true);
      }
    }

    function append(role, text, shouldPersist = true) {
      const bubble = document.createElement('div');
      bubble.style.margin = '6px 0';
      bubble.style.whiteSpace = 'pre-wrap';
      if (role === 'user') {
        bubble.style.textAlign = 'right';
        bubble.innerHTML = '<span style="display:inline-block;background:#2563eb;color:#fff;padding:6px 8px;border-radius:8px;">' + escapeHtml(text) + '</span>';
      } else {
        bubble.innerHTML = '<span style="display:inline-block;background:#f3f4f6;color:#111;padding:6px 8px;border-radius:8px;">' + escapeHtml(text) + '</span>';
      }
      messages.appendChild(bubble);
      messages.scrollTop = messages.scrollHeight;
      if (shouldPersist) {
        persist(role, text);
      }
      return bubble;
    }

    function escapeHtml(str) {
      return String(str).replace(/[&<>"]+/g, function (s) {
        return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[s]);
      });
    }

    function buildPayload(promptText) {
      const payload = { prompt: promptText };
      if (window.AI_COM_CONTEXT && typeof window.AI_COM_CONTEXT === 'object') {
        if (window.AI_COM_CONTEXT.userId) {
          payload.user_id = window.AI_COM_CONTEXT.userId;
        }
        if (Array.isArray(window.AI_COM_CONTEXT.productIds) && window.AI_COM_CONTEXT.productIds.length > 0) {
          payload.context = payload.context || {};
          payload.context.product_ids = window.AI_COM_CONTEXT.productIds;
        }
      }
      if (typeof window.AI_COM_ENRICH_PAYLOAD === 'function') {
        try {
          const extra = window.AI_COM_ENRICH_PAYLOAD(promptText) || {};
          Object.assign(payload, extra);
        } catch (err) {
          console.warn('AI_COM_ENRICH_PAYLOAD failed', err);
        }
      }
      return payload;
    }

    async function sendMessage(rawText) {
      const text = String(rawText || '').trim();
      if (!text) return;
      input.value = '';
      append('user', text, true);
      const placeholder = append('assistant', '...', false);
      try {
        const res = await fetch(api, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(buildPayload(text))
        });
        const data = await res.json();
        placeholder.remove();
        const reply = data.answer || data.error || 'No response';
        append('assistant', reply, true);
      } catch (err) {
        placeholder.remove();
        const failMsg = 'Error contacting assistant. Please try again.';
        append('assistant', failMsg, true);
      }
    }

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      sendMessage(input.value);
    });

    clearBtn.addEventListener('click', () => {
      clearHistory(false);
    });

    loadHistory();
    return root;
  }

  if (!document.getElementById('ai-chat-widget')) {
    const mount = function () {
      if (document.getElementById('ai-chat-widget')) return;
      document.body.appendChild(createChatWidget());
    };
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', mount);
    } else {
      mount();
    }
  }
})();

