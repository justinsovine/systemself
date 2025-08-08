// Export only client-side utilities
export { trpc, createTRPCClient, type AppRouter } from './trpc/client';

// Export validation schemas (these are safe for client-side)
export { z } from 'zod';

// Re-export shared types from shared-types package
export type { User, LoginRequest, RegisterRequest } from '@systemself/shared-types';

// Export tRPC client utilities
export { createTRPCReact } from '@trpc/react-query';
export { httpBatchLink } from '@trpc/client';