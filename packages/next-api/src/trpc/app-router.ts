import { router, publicProcedure, idSchema, createUserSchema } from './trpc';
import { z } from 'zod';

// User response schema
const userSchema = z.object({
  id: z.number(),
  name: z.string(),
  email: z.string().email(),
  email_verified_at: z.string().nullable(),
  created_at: z.string(),
  updated_at: z.string(),
});

export const appRouter = router({
  user: router({
    // Get user by ID - connects to GetUserQuery
    get: publicProcedure
      .input(idSchema)
      .output(userSchema)
      .query(async ({ input }) => {
        const response = await fetch(`http://localhost:7777/api/user/${input.id}`);
        const data = await response.json();
        
        if (!data.success) {
          throw new Error(data.message || 'Failed to fetch user');
        }
        
        return data.data;
      }),

    // Create user - connects to CreateUserCommand  
    create: publicProcedure
      .input(createUserSchema)
      .output(userSchema.pick({ id: true, name: true, email: true, created_at: true }))
      .mutation(async ({ input }) => {
        const response = await fetch('http://localhost:7777/api/user', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(input),
        });
        
        const data = await response.json();
        
        if (!data.success) {
          throw new Error(data.message || 'Failed to create user');
        }
        
        return data.data;
      }),
  }),
});

export type AppRouter = typeof appRouter;
