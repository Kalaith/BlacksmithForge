/// <reference types="vite/client" />

interface ImportMetaEnv {
  readonly VITE_API_URL: string;
  readonly VITE_ENV: string;
  readonly VITE_DEBUG: string;
  readonly VITE_ENABLE_FALLBACK: string;
  // more env variables...
}

interface ImportMeta {
  readonly env: ImportMetaEnv;
}
