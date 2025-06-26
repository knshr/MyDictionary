<template>
  <AuthLayout>
    <div>
      <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
        {{ $t('auth.signIn') }}
      </h2>
    </div>
    <form class="mt-8 space-y-6" @submit.prevent="submit">
      <div class="space-y-4">
        <InputField
          v-model="form.email"
          id="email"
          type="text"
          :label="$t('auth.email')"
          :placeholder="$t('auth.email')"
          required
          autocomplete="email"
        />
        <InputField
          v-model="form.password"
          id="password"
          type="password"
          :label="$t('auth.password')"
          :placeholder="$t('auth.password')"
          required
          autocomplete="password"
        />
      </div>

      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <input
            id="remember_me"
            v-model="form.remember"
            name="remember_me"
            type="checkbox"
            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
          />
          <label for="remember_me" class="ml-2 block text-sm text-gray-900">
            {{ $t('auth.rememberMe') }}
          </label>
        </div>
      </div>

      <div>
        <button
          type="submit"
          :disabled="form.processing"
          class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
        >
          <span v-if="form.processing">{{ $t('common.loading') }}</span>
          <span v-else>{{ $t('auth.signIn') }}</span>
        </button>
      </div>

      <div class="text-center">
        <Link href="/register" class="text-blue-600 hover:text-blue-500">
          {{ $t('auth.noAccount') }} {{ $t('auth.signUp') }}
        </Link>
      </div>
    </form>
  </AuthLayout>
</template>

<script setup>
  import { useForm, Link } from '@inertiajs/vue3';
  import AuthLayout from './AuthLayout.vue';
  import InputField from '@/components/InputField.vue';

  const form = useForm({
    email: '',
    password: '',
    remember: false,
  });

  const submit = () => {
    form.post('/login');
  };
</script>
