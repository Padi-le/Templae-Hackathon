// server.js
import express from 'express';
import rateLimit from 'express-rate-limit';
import dotenv from 'dotenv';
import { GoogleGenerativeAI } from '@google/generative-ai';

// Load environment variables from .env file
dotenv.config();


// Initialize Gemini AI
const genAI = new GoogleGenerativeAI(process.env.GEMINI_API_KEY);

const app = express();
import  http from 'http';
const server = http.createServer(app);
import { Server } from "socket.io";

const io = new Server(server, {
  cors: {
    origin: "*", 
  },
});

import path from 'path';
import { fileURLToPath } from 'url';
import { dirname, join } from 'path';
const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);
app.use(express.static(join(__dirname, 'public')));
app.use(express.json());

io.on('connection', (socket) => {
        console.log('A user connected');

        socket.on('disconnect', () => {
            console.log('User disconnected');
        });

        socket.on('chat message', (msg) => {
            console.log('message: ' + msg);
            io.emit('chat message', msg); // Broadcast to all connected clients
        });
    });

app.use((req, res, next) => {
  res.header('Access-Control-Allow-Origin', '*'); // In production, replace * with your domain
  res.header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
  res.header('Access-Control-Allow-Headers', 'Content-Type');
  if (req.method === 'OPTIONS') {
    return res.sendStatus(200);
  }
  next();
});

app.use(rateLimit({ windowMs: 60*1000, max: 30 }));

app.get('/test-auth', (req, res) => {
  const keyLastFour = GEMINI_API_KEY.slice(-4);
  res.json({ 
    status: 'API key loaded', 
    message: `API key ending in ...${keyLastFour} is loaded`
  });
});

const PORT = process.env.PORT || 3001;
const GEMINI_API_KEY = process.env.GEMINI_API_KEY;

if (!GEMINI_API_KEY) {
  console.error('Error: GEMINI_API_KEY is not set in environment variables');
  process.exit(1);
}

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
Keep answers friendly and concise (max ~300 words).include groceries list at the end of response if applicable
`;

const fewShot = [
  {role: "user", content: "Can you give me a quick vegetarian dinner under 30 minutes?"},
  {role: "assistant", content: "Sure — try a chickpea & spinach stir-fry: sauté onion and garlic, add spices, canned chickpeas, spinach... (full steps)"},
  {role: "user", content: "Who won the game last night?"},
  {role: "assistant", content: "I'm sorry — I only discuss meals and health. I can help with recipes, nutrition tips, meal plans, and similar topics."}
];

app.post("/chat", async (req, res) => {
  try {
    console.log('Received request:', req.body); // Add request logging
    const userMessage = (req.body.message || "").trim();
    if (!userMessage) return res.status(400).json({ error: "No message" });

    // quick off-topic block
    if (isOffTopic(userMessage)) {
      return res.json({
        reply: "I'm sorry — I only discuss meals and health. I can help with recipes, nutrition tips, meal plans, and similar topics."
      });
    }

    // Get the generative model
    const model = genAI.getGenerativeModel({ model: "gemini-2.5-flash" });
    console.log('Received message:', userMessage); // Add logging

    // Create the prompt with the system prompt and user message
    const prompt = systemPrompt + "\n\nUser query: " + userMessage;

    try {
      // Generate content
      const result = await model.generateContent(prompt);
      const response = await result.response;
      const assistantReply = response.text();

      if (!assistantReply || isOffTopic(assistantReply)) {
        return res.json({
          reply: "I'm sorry — I can only discuss meals and health. Ask me about recipes, nutrition, grocery lists or meal plans!"
        });
      }
      console.log(assistantReply);
      res.json({ reply: assistantReply });
    } catch (apiError) {
      console.error('Gemini API Error:', apiError);
      res.status(500).json({ 
        error: "AI service error", 
        details: apiError.message 
      });
    }
  } catch (err) {
    console.error('Server Error:', err);
    res.status(500).json({ error: "Server error" });
  }
});


server.listen(PORT, () => console.log(`AI chat API running on port ${PORT}`));

// app.listen(PORT, () => console.log(`AI chat API running on port ${PORT}`));
