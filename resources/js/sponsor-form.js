// ── スポンサー登録フォーム：ページを読み込み直さずに送信する ──
// 普通に送信すると、エラーで戻ったときにファイル選択が空になる（ブラウザの仕様で復元できない）。
// fetch で裏から送り、エラーはこの画面のまま表示することで、選んだファイルを残す。
// JS が動かないときは、今までどおり普通の送信になる。

const form = document.getElementById('sponsor-form');

if (form) {
    const summary = document.getElementById('error-summary');
    const summaryList = summary?.querySelector('ul');
    const submitButton = form.querySelector('button[type="submit"]');
    const fileInput = form.querySelector('input[name="media"]');
    const maxMb = Number(form.dataset.maxMb) || 50;
    const sizeMessage = `画像・動画は${maxMb}MB以下のファイルを選んでください。`;

    // エラー表示をすべて消す
    function clearErrors() {
        form.querySelectorAll('[data-error-for]').forEach((el) => { el.textContent = ''; });
        if (summaryList) summaryList.innerHTML = '';
        summary?.classList.add('hidden');
    }

    // errors：{ 項目名: [メッセージ, …] } の形（Laravel の 422 レスポンスと同じ形）
    function showErrors(errors) {
        clearErrors();
        Object.entries(errors).forEach(([field, messages]) => {
            const fieldError = form.querySelector(`[data-error-for="${field}"]`);
            if (fieldError) fieldError.textContent = messages[0];

            messages.forEach((message) => {
                const li = document.createElement('li');
                li.textContent = message; // textContent なので HTML として解釈されない
                summaryList?.appendChild(li);
            });
        });
        summary?.classList.remove('hidden');
        summary?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function setSending(sending) {
        if (!submitButton) return;
        submitButton.disabled = sending; // 送信中は押せなくして二重登録を防ぐ
        submitButton.textContent = sending ? '送信中…' : '登録';
    }

    // 50MB を超えるファイルは、選んだ時点でエラーを出す
    // （100MB を超えると PHP がバリデーションの前で止めるので、ここで止めておく）
    function fileTooLarge() {
        const file = fileInput?.files[0];
        return file ? file.size > maxMb * 1024 * 1024 : false;
    }

    fileInput?.addEventListener('change', () => {
        if (fileTooLarge()) {
            showErrors({ media: [sizeMessage] });
        } else {
            clearErrors();
        }
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (fileTooLarge()) {
            showErrors({ media: [sizeMessage] });
            return;
        }

        setSending(true);

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form), // @csrf のトークンも一緒に送られる
                headers: { Accept: 'application/json' }, // Laravel にエラーを JSON で返してもらう
            });

            // 返ってきた中身が JSON でなくても止まらないよう、読めなければ空にする
            const data = await response.json().catch(() => ({}));

            if (response.ok && data.redirect) {
                window.location.href = data.redirect; // 成功：一覧画面へ（メッセージは一覧で表示される）
                return;
            }

            if (response.status === 422 && data.errors) {
                showErrors(data.errors); // バリデーションエラー
            } else if (response.status === 413) {
                showErrors({ media: [sizeMessage] }); // PHP の上限を超えた
            } else if (response.status === 419) {
                showErrors({ form: ['画面の有効期限が切れました。ページを再読み込みしてから、もう一度登録してください。'] });
            } else {
                showErrors({ form: [`登録できませんでした（エラー ${response.status}）。時間をおいてもう一度お試しください。`] });
            }
        } catch {
            showErrors({ form: ['通信できませんでした。ネットワークを確認して、もう一度お試しください。'] });
        }

        setSending(false);
    });
}
