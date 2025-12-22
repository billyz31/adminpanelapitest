/// <reference types="vite/client" />
declare module '*.vue' {
  import type { DefineComponent } from 'vue'
  const component: DefineComponent<{}, {}, any>
  export default component
}

interface Window {
  turnstile: {
    render: (selector: string | HTMLElement, options: any) => void;
    reset: (widgetId?: string) => void;
    getResponse: (widgetId?: string) => string | undefined;
  };
}
