import { createTRPCReact, httpBatchLink } from '@systemself/next-api';
import type { AppRouter } from '@systemself/next-api';

export const trpc = createTRPCReact<AppRouter>();

export const trpcClient = trpc.createClient({
  links: [
    httpBatchLink({
      url: 'http://localhost:7777/api',
    }),
  ],
});