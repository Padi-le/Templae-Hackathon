// server.js
import express from "express";
import fetch from "node-fetch"; 
import rateLimit from "express-rate-limit";

const app = express();
app.use(express.json());

// simple rate limit
app.use(rateLimit({ windowMs: 60*1000, max: 30 }));

const PORT = process.env.PORT || 3000;
const LLM_API_KEY = process.env.LLM_API_KEY; // store securely

// simple topic check - tune this list to your needs
function isOffTopic(text) {
  const off = [
    "politic", "president", "election", "salary", "bank", "bitcoin",
    "bomb", "kill", "porn", "sex", "drugs", "assassin", "attack"
  ];
  const t = text.toLowerCase();
  return off.some(w => t.includes(w));
}

const systemPrompt = `
You are MealHealthBot. You MUST only talk about meals, nutrition, recipes, meal planning, dietary advice (non-medical),
cooking techniques, grocery lists, healthy habits and related food & wellness topics. 
If a user asks anything outside meals/health, politely refuse with:
"I'm sorry — I only discuss meals and health. I can help with recipes, nutrition tips, meal plans, and similar topics."
Do not give medical diagnoses. For medical questions, include a short disclaimer: "I am not a doctor; consult a professional."
Keep answers friendly and concise (max ~300 words).
`;

// few-shot examples
const fewShot = [
  {role: "user", content: "Can you give me a quick vegetarian dinner under 30 minutes?"},
  {role: "assistant", content: "Sure — try a chickpea & spinach stir-fry: sauté onion and garlic, add spices, canned chickpeas, spinach... (full steps)"},
  {role: "user", content: "Who won the game last night?"},
  {role: "assistant", content: "I'm sorry — I only discuss meals and health. I can help with recipes, nutrition tips, meal plans, and similar topics."}
];

app.post("/chat", async (req, res) => {
  try {
    const userMessage = (req.body.message || "").trim();
    if (!userMessage) return res.status(400).json({ error: "No message" });

    // quick off-topic block
    if (isOffTopic(userMessage)) {
      return res.json({
        reply: "I'm sorry — I only discuss meals and health. I can help with recipes, nutrition tips, meal plans, and similar topics."
      });
    }

    // build messages
    const messages = [
      { role: "system", content: systemPrompt },
      ...fewShot,
      { role: "user", content: userMessage }
    ];

    const llmResp = await fetch("https://api.openai.com/v1/chat/completions", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "Authorization": `Bearer ${LLM_API_KEY}`
      },
      body: JSON.stringify({
        model: "gpt-4o-mini", 
        messages,
        max_tokens: 500,
        temperature: 0.7
      })
    });

    if (!llmResp.ok) {
      const err = await llmResp.text();
      console.error("LLM error:", err);
      return res.status(500).json({ error: "LLM error" });
    }

    const data = await llmResp.json();
    const assistantReply = data.choices?.[0]?.message?.content?.trim() ?? "";

    if (!assistantReply || isOffTopic(assistantReply)) {
      return res.json({
        reply: "I'm sorry — I can only discuss meals and health. Ask me about recipes, nutrition, grocery lists or meal plans!"
      });
    }

    res.json({ reply: assistantReply });
  } catch (err) {
    console.error(err);
    res.status(500).json({ error: "Server error" });
  }
});

app.listen(PORT, () => console.log(`AI chat API running on port ${PORT}`));
