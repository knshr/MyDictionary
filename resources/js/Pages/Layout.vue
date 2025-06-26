<template>
  <div class="min-h-screen bg-gray-100">
    <nav class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex">
            <div class="flex-shrink-0 flex items-center">
              <h1 class="text-xl font-bold text-gray-900">
                {{ $t('navigation.appName') }}
              </h1>
            </div>
          </div>
          <div class="flex items-center">
            <div
              v-if="$page.props.auth.user"
              class="flex items-center space-x-4"
            >
              <span class="text-gray-700">{{
                $page.props.auth.user.name
              }}</span>
              <button @click="logout" class="btn btn-secondary">
                {{ $t('auth.logout') }}
              </button>
            </div>
            <div v-else class="flex items-center space-x-4">
              <Link href="/login" class="btn btn-primary">{{
                $t('auth.login')
              }}</Link>
              <Link href="/register" class="btn btn-secondary">{{
                $t('auth.register')
              }}</Link>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <main class="py-10">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <slot />
      </div>
    </main>
  </div>
</template>

<script setup>
  import { Link } from '@inertiajs/vue3';
  import { router } from '@inertiajs/vue3';

  const logout = () => {
    router.post('/logout');
  };
</script>
