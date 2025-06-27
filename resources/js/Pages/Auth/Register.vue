<template>
  <AuthLayout>
    <div>
      <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
        {{ showOtpForm ? $t('auth.verifyEmail') : $t('auth.signUp') }}
      </h2>
      <p v-if="showOtpForm" class="mt-2 text-center text-sm text-gray-600">
        {{ $t('auth.otpSentTo') }} {{ authStore.otpEmail }}
      </p>
    </div>

    <!-- Registration Form -->
    <form v-if="!showOtpForm" class="mt-8 space-y-6" @submit.prevent="submit">
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

    <!-- OTP Form -->
    <form v-if="showOtpForm" class="mt-8 space-y-6" @submit.prevent="submitOtp">
      <div class="space-y-4">
        <InputField
          v-model="otpForm.otp"
          id="otp"
          type="text"
          :label="$t('auth.otpCode')"
          :placeholder="$t('auth.otpPlaceholder')"
          required
          maxlength="6"
          class="text-center text-2xl tracking-widest"
        />
      </div>

      <div class="space-y-4">
        <button
          type="submit"
          :disabled="otpForm.processing"
          class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
        >
          <span v-if="otpForm.processing">{{ $t('common.loading') }}</span>
          <span v-else>{{ $t('auth.verifyOtp') }}</span>
        </button>

        <button
          type="button"
          :disabled="resendLoading || !canResendOtp || resendCooldown > 0"
          @click="resendOtp"
          class="w-full flex justify-center py-2 px-4 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
        >
          <span v-if="resendLoading">{{ $t('common.sending') }}</span>
          <span v-else-if="resendCooldown > 0"
            >{{ $t('auth.resendOtp') }} ({{ formatTime(resendCooldown) }})</span
          >
          <span v-else>{{ $t('auth.resendOtp') }}</span>
        </button>

        <button
          type="button"
          @click="backToRegister"
          class="w-full flex justify-center py-2 px-4 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
          {{ $t('auth.backToRegister') }}
        </button>
      </div>

      <div
        v-if="authStore.cooldownMessage"
        class="text-orange-600 text-sm text-center"
      >
        {{ authStore.cooldownMessage }}
      </div>

      <div
        v-if="authStore.requestLimitMessage"
        class="text-red-600 text-sm text-center"
      >
        {{ authStore.requestLimitMessage }}
      </div>
    </form>
  </AuthLayout>
</template>

<script setup>
  import { useForm, Link, router } from '@inertiajs/vue3';
  import { ref, computed, onMounted, onUnmounted } from 'vue';
  import { useI18n } from 'vue-i18n';
  import AuthLayout from './AuthLayout.vue';
  import InputField from '@/components/InputField.vue';
  import { useAuthStore } from '@/stores/auth';
  import { useToastStore } from '@/stores/toast';

  const { t } = useI18n();
  const authStore = useAuthStore();
  const toastStore = useToastStore();

  const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
  });

  const otpForm = useForm({
    otp: '',
  });

  const resendLoading = ref(false);
  const resendCooldown = ref(0);
  let cooldownTimer = null;

  const showOtpForm = computed(() => authStore.requiresOtp);

  const canResendOtp = computed(() => {
    return authStore.canResendOtp && resendCooldown.value <= 0;
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

  const formatTime = seconds => {
    if (!seconds || seconds <= 0) return '0:00';
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = seconds % 60;
    return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
  };

  const startCooldownTimer = seconds => {
    resendCooldown.value = seconds;
    cooldownTimer = setInterval(() => {
      resendCooldown.value--;
      if (resendCooldown.value <= 0) {
        clearInterval(cooldownTimer);
        cooldownTimer = null;
      }
    }, 1000);
  };

  const restoreOtpState = async () => {
    try {
      const restored = await authStore.restoreOtpState();
      if (restored) {
        // If OTP state was restored, start the cooldown timer
        // We'll use the remaining cooldown time from the server if available
        const cooldownTime =
          authStore.otpStatus.resend_cooldown_remaining_seconds;
        if (cooldownTime > 0) {
          startCooldownTimer(cooldownTime);
        } else {
          // If no cooldown, start a short timer to prevent immediate resend
          startCooldownTimer(30);
        }

        toastStore.showToast({
          message: t('auth.otpSessionRestored'),
          type: 'info',
        });
      }
    } catch (error) {
      console.error('Error restoring OTP state:', error);
    }
  };

  const submit = async () => {
    if (
      !form.name ||
      !form.email ||
      !form.password ||
      !form.password_confirmation
    )
      return; // InputField handles toasts

    // Check if there's already a pending OTP state
    const existingOtpState = authStore.loadOtpState();
    if (existingOtpState && existingOtpState.email === form.email) {
      // If there's already an OTP state for this email, restore it instead of making a new registration request
      const restored = await authStore.restoreOtpState();
      if (restored) {
        // Start the cooldown timer
        const cooldownTime =
          authStore.otpStatus.resend_cooldown_remaining_seconds;
        if (cooldownTime > 0) {
          startCooldownTimer(cooldownTime);
        } else {
          startCooldownTimer(30);
        }

        toastStore.showToast({
          message: t('auth.otpSessionRestored'),
          type: 'info',
        });
        return;
      }
    }

    try {
      const result = await authStore.register({
        name: form.name,
        email: form.email,
        password: form.password,
        password_confirmation: form.password_confirmation,
      });

      if (result.success) {
        if (result.requiresOtp) {
          // Start cooldown timer when OTP is first sent
          startCooldownTimer(60); // 60 seconds cooldown
        }
      } else {
        toastStore.showToast({
          message: result.message,
          type: 'error',
        });
      }
    } catch (error) {
      toastStore.showToast({
        message: 'An unexpected error occurred',
        type: 'error',
      });
    }
  };

  const submitOtp = async () => {
    try {
      const result = await authStore.verifyRegistrationOtp(otpForm.otp);

      if (result.success) {
        // OTP verification successful, user will be redirected to dashboard
        console.log('OTP verification successful');
        toastStore.showToast({
          message: 'Registration successful! Welcome to MyDictionary.',
          type: 'success',
        });

        // Redirect to dashboard using Inertia router
        router.visit('/dashboard');
      } else {
        toastStore.showToast({
          message: result.message,
          type: 'error',
        });
      }
    } catch (error) {
      console.error('OTP verification failed:', error);
      toastStore.showToast({
        message: 'OTP verification failed',
        type: 'error',
      });
    }
  };

  const backToRegister = () => {
    authStore.requiresOtp = false;
    authStore.otpEmail = '';
    otpForm.otp = '';
    authStore.clearOtpState();
    if (cooldownTimer) {
      clearInterval(cooldownTimer);
      cooldownTimer = null;
    }
    resendCooldown.value = 0;
  };

  const resendOtp = async () => {
    resendLoading.value = true;

    try {
      const result = await authStore.resendOtp('register');
      if (result.success) {
        toastStore.showToast({
          message: 'OTP sent successfully!',
          type: 'success',
        });
        // Start cooldown timer after successful resend
        startCooldownTimer(60); // 60 seconds cooldown
      } else {
        if (result.errors && result.errors.otp) {
          toastStore.showToast({
            message: result.errors.otp[0],
            type: 'error',
          });
        } else {
          toastStore.showToast({
            message: result.message,
            type: 'error',
          });
        }
      }
    } catch (error) {
      toastStore.showToast({
        message: 'Failed to resend OTP',
        type: 'error',
      });
    } finally {
      resendLoading.value = false;
    }
  };

  onMounted(async () => {
    // Check for and restore OTP state on component mount
    await restoreOtpState();
  });

  onUnmounted(() => {
    if (cooldownTimer) {
      clearInterval(cooldownTimer);
    }
  });
</script>
