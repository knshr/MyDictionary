<template>
  <AuthLayout>
    <div>
      <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
        {{ $t('auth.signUp') }}
      </h2>
    </div>
    <form class="mt-8 space-y-6" @submit.prevent="submit">
      <div class="space-y-4">
        <InputField
          v-model="form.name"
          id="name"
          type="text"
          label="Name"
          placeholder="Name"
          required
          autocomplete="name"
        />
        <InputField
          v-model="form.email"
          id="email"
          type="email"
          label="Email"
          placeholder="Email"
          required
          :rules="emailRules"
          autocomplete="email"
        />
        <InputField
          v-model="form.password"
          id="password"
          type="password"
          label="Password"
          placeholder="Password"
          required
          :rules="passwordRules"
          autocomplete="new-password"
        />
        <InputField
          v-model="form.password_confirmation"
          id="password_confirmation"
          type="password"
          label="Confirm Password"
          placeholder="Confirm Password"
          required
          :rules="passwordConfirmationRules"
          autocomplete="new-password"
        />
      </div>
      <div>
        <button
          type="submit"
          :disabled="form.processing"
          class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
        >
          <span v-if="form.processing">{{ $t('common.loading') }}</span>
          <span v-else>{{ $t('auth.signUp') }}</span>
        </button>
      </div>
      <div class="text-center">
        <Link href="/login" class="text-blue-600 hover:text-blue-500">
          {{ $t('auth.hasAccount') }} {{ $t('auth.signIn') }}
        </Link>
      </div>
    </form>
  </AuthLayout>
</template>

<script setup>
  import { useForm, Link } from '@inertiajs/vue3';
  import AuthLayout from './AuthLayout.vue';
  import InputField from '@/components/InputField.vue';
  import { ref } from 'vue';

  const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
  });

  const emailRules = [
    {
      validator: v => /^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(v),
      message: 'Please enter a valid email address',
    },
  ];
  const passwordRules = [
    {
      validator: v => v && v.length >= 8,
      message: 'Password must be at least 8 characters',
    },
  ];
  const passwordConfirmationRules = [
    {
      validator: v => v === form.password,
      message: 'Passwords do not match',
    },
  ];

  const submit = () => {
    if (
      !form.name ||
      !form.email ||
      !form.password ||
      !form.password_confirmation
    )
      return; // InputField handles toasts
    form.post('/register');
  };
</script>
