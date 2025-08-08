import { createTRPCReact } from '@trpc/react-query';
import { httpBatchLink } from '@trpc/client';
import type { appRouter } from './app-router';

export type AppRouter = typeof appRouter;
export const trpc = createTRPCReact<AppRouter>();

export const createTRPCClient = () => trpc.createClient({
    links: [
        httpBatchLink({
            url: 'http://localhost:7777/api/trpc',
        }),
    ],
});