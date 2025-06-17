export default async function handler(req, res) {
    if (req.method !== 'POST') {
      return res.status(405).json({ reply: 'Hanya menerima POST' });
    }
  
    const { message } = req.body;
    const apiKey = 'AIzaSyDxYqHU2fiBu9OvA8Hw9y3NlV1gcahrH-o'; // Ganti dengan API Key Gemini kamu
    const prompt = `${message}\n\nTolong berikan jawaban lengkap berdasarkan jurnal ilmiah setelah tahun 2020 dan cantumkan tautan DOI atau link asli jurnalnya jika tersedia.`;
  
    try {
      const result = await fetch(`https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=${apiKey}`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          contents: [
            {
              parts: [
                { text: prompt }
              ]
            }
          ]
        })
      });
  
      const json = await result.json();
      const reply = json?.candidates?.[0]?.content?.parts?.[0]?.text ?? '[Tidak ada balasan]';
      res.status(200).json({ reply });
    } catch (err) {
      res.status(500).json({ reply: 'Gagal menghubungi API Gemini.' });
    }
  }
  