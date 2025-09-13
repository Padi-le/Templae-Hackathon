import { GoogleGenAI } from "@google/genai";

const ai = new GoogleGenAI({});

async function main(){
    const response = await ai.models.generateContent({
        model: "gemini-2.5-flash",
        contents: "Ai chatbot that will discribe how many calories a meal will have",
    });
    console.log(response.text);
}

main();