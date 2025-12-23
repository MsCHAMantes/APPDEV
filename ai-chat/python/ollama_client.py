import os
from typing import Any, Dict, List, Optional

import requests


class OllamaClient:
    """Simple HTTP client for talking to the local Ollama server."""

    def __init__(self) -> None:
        self.host = os.getenv("OLLAMA_HOST", "127.0.0.1")
        self.port = os.getenv("OLLAMA_PORT", "11434")
        self.model = os.getenv("OLLAMA_MODEL", "naruto")
        self.system_prompt = os.getenv("OLLAMA_SYSTEM_PROMPT")
        self.timeout = float(os.getenv("OLLAMA_TIMEOUT", "30"))

    def chat(
        self,
        messages: List[Dict[str, str]],
        *,
        stream: bool = False,
        extra_options: Optional[Dict[str, Any]] = None,
    ) -> Dict[str, Any]:
        payload: Dict[str, Any] = {
            "model": self.model,
            "messages": self._with_system_prompt(messages),
            "stream": stream,
        }
        if extra_options:
            payload.update(extra_options)
        return self._request("/api/chat", payload)

    def generate(
        self,
        prompt: str,
        *,
        stream: bool = False,
        extra_options: Optional[Dict[str, Any]] = None,
    ) -> Dict[str, Any]:
        payload: Dict[str, Any] = {
            "model": self.model,
            "prompt": prompt,
            "stream": stream,
        }
        if extra_options:
            payload.update(extra_options)
        return self._request("/api/generate", payload)

    def _with_system_prompt(self, messages: List[Dict[str, str]]) -> List[Dict[str, str]]:
        normalized = [
            {"role": str(msg.get("role", "")), "content": str(msg.get("content", ""))}
            for msg in messages
            if msg.get("role") and msg.get("content")
        ]
        if self.system_prompt:
            normalized.insert(0, {"role": "system", "content": self.system_prompt})
        return normalized

    def _request(self, path: str, payload: Dict[str, Any]) -> Dict[str, Any]:
        url = f"http://{self.host}:{self.port}{path}"
        response = requests.post(url, json=payload, timeout=self.timeout)
        response.raise_for_status()
        return response.json()

