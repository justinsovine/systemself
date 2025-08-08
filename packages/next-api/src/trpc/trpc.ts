import { initTRPC } from '@trpc/server';
import { z } from 'zod';

// Initialize tRPC
const t = initTRPC.create();

// Export reusable router and procedure helpers
export const router = t.router;
export const publicProcedure = t.procedure;

// Input validation helpers
export const idSchema = z.object({
  id: z.number().int().positive()
});

export const createUserSchema = z.object({
  name: z.string().min(2),
  email: z.string().email(),
  password: z.string().min(8)
});
