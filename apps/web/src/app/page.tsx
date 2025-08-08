'use client';

import { trpc } from '@/lib/trpc/client';
import { useState } from 'react';

export default function Home() {
  const [userId, setUserId] = useState(1);

  // Type-safe query using your Laravel GetUserQuery handler
  const { data: user, isLoading, error } = trpc.user.get.useQuery({ id: userId });

  // Type-safe mutation using your Laravel CreateUserCommand handler
  const createUser = trpc.user.create.useMutation();

  const handleCreateUser = async () => {
    try {
      const newUser = await createUser.mutateAsync({
        name: 'New User',
        email: `user${Date.now()}@systemself.local`,
        password: 'password123'
      });
      console.log('Created user:', newUser);
      setUserId(newUser.id);
    } catch (error) {
      console.error('Failed to create user:', error);
    }
  };

  return (
      <div className="grid grid-rows-[20px_1fr_20px] items-center justify-items-center min-h-screen p-8 pb-20 gap-16 sm:p-20">
        <main className="flex flex-col gap-8 row-start-2 items-center">
          <h1 className="text-4xl font-bold">SystemSelf</h1>

          <div className="flex flex-col gap-4 items-center">
            <div className="flex gap-2">
              <input
                  type="number"
                  value={userId}
                  onChange={(e) => setUserId(Number(e.target.value))}
                  className="px-3 py-2 border rounded"
                  placeholder="User ID"
              />
              <button
                  onClick={() => setUserId(userId)}
                  className="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
              >
                Load User
              </button>
            </div>

            {isLoading && <p>Loading user...</p>}
            {error && <p className="text-red-500">Error: {error.message}</p>}
            {user && (
                <div className="p-4 border rounded">
                  <h2 className="font-semibold">{user.name}</h2>
                  <p>{user.email}</p>
                  <p className="text-sm text-gray-500">ID: {user.id}</p>
                </div>
            )}

            <button
                onClick={handleCreateUser}
                disabled={createUser.isPending}
                className="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 disabled:opacity-50"
            >
              {createUser.isPending ? 'Creating...' : 'Create New User'}
            </button>
          </div>
        </main>
      </div>
  );
}