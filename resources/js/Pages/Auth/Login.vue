<template>
  <AuthLayout>
    <div>
      <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
        {{ showOtpForm ? $t('auth.enterOtp') : $t('auth.signIn') }}
      </h2>
      <p v-if="showOtpForm" class="mt-2 text-center text-sm text-gray-600">
        {{ $t('auth.otpSentTo') }} {{ authStore.otpEmail }}
      </p>
    </div>

    <!-- Login Form -->
    <form v-if="!showOtpForm" class="mt-8 space-y-6" @submit.prevent="submit">
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
          @click="backToLogin"
          class="w-full flex justify-center py-2 px-4 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
          {{ $t('auth.backToLogin') }}
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
    email: '',
    password: '',
    remember: false,
  });

  const otpForm = useForm({
    otp: '',
  });

  const resendLoading = ref(false);
  const resendCooldown = ref(0);
  let cooldownTimer = null;

  const showOtpForm = computed(() => {
    console.log('showOtpForm computed - requiresOtp:', authStore.requiresOtp);
    return authStore.requiresOtp;
  });

  const canResendOtp = computed(() => {
    return authStore.canResendOtp && resendCooldown.value <= 0;
  });

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
    // Check if there's already a pending OTP state
    const existingOtpState = authStore.loadOtpState();
    console.log('Existing OTP state:', existingOtpState);
    console.log('Form email:', form.email);

    if (existingOtpState && existingOtpState.email === form.email) {
      console.log('Found existing OTP state for this email, restoring...');
      // If there's already an OTP state for this email, restore it instead of making a new login request
      const restored = await authStore.restoreOtpState();
      console.log('OTP state restored:', restored);
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

    console.log('No existing OTP state found, proceeding with login...');
    console.log('Submitting login using Inertia form...');

    // Use Inertia's form handling for proper session management
    router.post(
      '/login',
      {
        email: form.email,
        password: form.password,
        remember: form.remember,
      },
      {
        onSuccess: page => {
          console.log('Login successful');
          // Check if OTP is required by looking at the session data
          // If we're still on the login page, OTP is likely required
          if (window.location.pathname === '/login') {
            console.log('OTP required, showing OTP form');
            authStore.requiresOtp = true;
            authStore.otpEmail = form.email;
            // Start cooldown timer when OTP is first sent
            startCooldownTimer(60);
          }
        },
        onError: errors => {
          console.error('Login failed:', errors);
          toastStore.showToast({
            message: errors.email || errors.password || 'Login failed',
            type: 'error',
          });
        },
      }
    );
  };

  const submitOtp = async () => {
    try {
      console.log('Submitting OTP using Inertia form...');

      // Use Inertia's form handling for proper session management
      router.post(
        '/verify-otp',
        {
          otp: otpForm.otp,
        },
        {
          onSuccess: () => {
            console.log(
              'OTP verification successful, redirecting to dashboard...'
            );
            toastStore.showToast({
              message: 'Login successful! Welcome back.',
              type: 'success',
            });
          },
          onError: errors => {
            console.error('OTP verification failed:', errors);
            toastStore.showToast({
              message: errors.otp || 'OTP verification failed',
              type: 'error',
            });
          },
        }
      );
    } catch (error) {
      console.error('OTP verification failed:', error);
      toastStore.showToast({
        message: 'OTP verification failed',
        type: 'error',
      });
    }
  };

  const backToLogin = () => {
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
      const result = await authStore.resendOtp('login');
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
    // Clear any existing OTP state to start fresh
    authStore.clearOtpState();
    authStore.requiresOtp = false;
    authStore.otpEmail = '';

    // Check for and restore OTP state on component mount
    // await restoreOtpState();
  });

  onUnmounted(() => {
    if (cooldownTimer) {
      clearInterval(cooldownTimer);
    }
  });
</script>
