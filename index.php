<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userMessage = $_POST['message'] ?? '';
    $apiKey = 'AIzaSyDxYqHU2fiBu9OvA8Hw9y3NlV1gcahrH-o'; // Ganti dengan API key milikmu

    // Tambahkan instruksi agar AI menyertakan link referensi jurnal
    $customPrompt = $userMessage . "\n\nTolong berikan jawaban lengkap berdasarkan jurnal ilmiah setelah tahun 2020 dan cantumkan tautan DOI atau link asli jurnalnya jika tersedia.";

    $postData = [
        'contents' => [
            [
                'parts' => [
                    ['text' => $customPrompt]
                ]
            ]
        ]
    ];

    $ch = curl_init("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=$apiKey");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));

    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        echo json_encode(['reply' => 'Error: ' . $error]);
    } else {
        $responseData = json_decode($response, true);
        $reply = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? 'Tidak ada balasan.';
        echo json_encode(['reply' => $reply]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>AI Chat (Gemini + Referensi)</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen flex flex-col items-center p-4">
    <div class="w-full max-w-xl bg-white rounded-lg shadow-lg p-4 flex flex-col h-[90vh]">
        <h1 class="text-2xl font-bold mb-4 text-center text-blue-700">AI Chat with Referensi Jurnal</h1>

        <div id="chat" class="flex-1 overflow-y-auto space-y-2 mb-4 p-2 border rounded bg-gray-50"></div>

        <form id="chat-form" class="flex space-x-2">
            <input id="user-input" name="message" type="text" placeholder="Tulis pertanyaan..."
                class="flex-1 p-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500" />
            <button type="submit"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">Kirim</button>
        </form>
    </div>

    <script>
        const form = document.getElementById('chat-form');
        const input = document.getElementById('user-input');
        const chat = document.getElementById('chat');

        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            const userMessage = input.value.trim();
            if (!userMessage) return;

            appendMessage('user', userMessage);
            input.value = '';

            appendMessage('assistant', 'Mengetik...');
            const loadingEl = chat.lastChild;

            try {
                const res = await fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        message: userMessage
                    })
                });

                const data = await res.json();
                loadingEl.remove();
                appendMessage('assistant', data.reply || '[Kosong]');
            } catch (err) {
                loadingEl.remove();
                appendMessage('assistant', 'Terjadi kesalahan saat menghubungi API.');
            }
        });

        function appendMessage(role, text) {
            const div = document.createElement('div');
            div.className = `p-2 rounded whitespace-pre-wrap ${
        role === 'user' ? 'bg-blue-100 self-end text-right' : 'bg-gray-200 self-start text-left'
      }`;
            div.innerHTML = role === 'assistant' ? formatTextToHTML(text) : text;
            chat.appendChild(div);
            chat.scrollTop = chat.scrollHeight;
        }

        function formatTextToHTML(text) {
            const urlRegex = /(https?:\/\/[^\s]+)/g;

            // Hapus markdown bold dan tautan dalam format markdown
            text = text.replace(/\*\*(.*?)\*\*/g, '$1');
            text = text.replace(/\[(.*?)\]\((.*?)\)/g, '$1 ($2)');

            const lines = text.split('\n');
            let html = '';
            let entry = [];
            let numbering = 1;

            const flushEntry = () => {
                if (entry.length === 0) return;

                html += `<div class="border-t border-gray-300 pt-4 mt-4">`;

                const title = entry[0];
                html += `<p class="font-bold text-lg mb-2">${numbering++}. ${title}</p>`;
                html += `<ul class="list-disc list-inside space-y-1 text-sm text-gray-800">`;

                for (let i = 1; i < entry.length; i++) {
                    const line = entry[i].trim();

                    if (line.match(urlRegex)) {
                        html += `</ul><p class="mt-2"><a href="${line}" target="_blank" class="text-blue-600 underline break-all">${line}</a></p><ul>`;
                    } else if (line.length > 0) {
                        html += `<li>${line}</li>`;
                    }
                }

                html += `</ul></div>`;
                entry = [];
            };

            for (let line of lines) {
                line = line.trim();

                if (/^\d+\.\s+/.test(line)) {
                    flushEntry();
                    entry.push(line.replace(/^\d+\.\s+/, ''));
                } else if (line) {
                    if (line.match(urlRegex)) {
                        const links = line.match(urlRegex);
                        links.forEach(link => entry.push(link));
                    } else {
                        entry.push(line);
                    }
                }
            }

            flushEntry();
            return html;
        }
    </script>
</body>

</html>