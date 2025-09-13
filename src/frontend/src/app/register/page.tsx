"use client";
import { useState } from "react";

export default function RegisterPage() {
    const [name, setName] = useState("");
    const [surname, setSurname] = useState("");
    const [username, setUsername] = useState("");
    const [email, setEmail] = useState("");
    const [password, setPassword] = useState("");

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        console.log("Name:", name, "Surname:", surname, "Username:", username, "Email:", email, "Password:", password);
    };

    return (
        <div className="flex h-screen items-center justify-center bg-gray-100">
            <div className="w-full max-w-md bg-white shadow-lg rounded-2xl p-8">
                <h2 className="text-2xl font-bold text-center mb-6">Register</h2>
                <form onSubmit={handleSubmit} className="space-y-4">
                    <input
                        type="text"
                        placeholder="Name"
                        value={name}
                        onChange={(e) => setName(e.target.value)}
                        className="w-full p-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                    />
                    <input
                        type="text"
                        placeholder="Surname"
                        value={surname}
                        onChange={(e) => setSurname(e.target.value)}
                        className="w-full p-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                    />
                    <input
                        type="text"
                        placeholder="Username"
                        value={username}
                        onChange={(e) => setUsername(e.target.value)}
                        className="w-full p-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                    />
                    <input
                        type="email"
                        placeholder="Email"
                        value={email}
                        onChange={(e) => setEmail(e.target.value)}
                        className="w-full p-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                    />
                    <input
                        type="password"
                        placeholder="Password"
                        value={password}
                        onChange={(e) => setPassword(e.target.value)}
                        className="w-full p-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                    />
                    <button
                        type="submit"
                        className="w-full bg-blue-600 text-white p-3 rounded-xl hover:bg-blue-700 transition"
                    >
                        Register
                    </button>
                </form>
            </div>
        </div>
    );
}