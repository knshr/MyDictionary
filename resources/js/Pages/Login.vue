<template>
  <div
    class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8"
  >
    <div class="max-w-md w-full space-y-8">
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          {{ showOtpForm ? 'Enter OTP Code' : 'Sign in to your account' }}
        </h2>
        <p v-if="showOtpForm" class="mt-2 text-center text-sm text-gray-600">
          We've sent a verification code to {{ authStore.otpEmail }}
        </p>
      </div>

      <!-- Login Form -->
      <form
        v-if="!showOtpForm"
        class="mt-8 space-y-6"
        @submit.prevent="submitLogin"
      >
        <div class="rounded-md shadow-sm -space-y-px">
          <div>
            <label for="email" class="sr-only">Email address</label>
            <input
              id="email"
              v-model="loginForm.email"
              name="email"
              type="email"
              required
              class="form-input rounded-t-md"
              placeholder="Email address"
            />
          </div>
          <div>
            <label for="password" class="sr-only">Password</label>
            <input
              id="password"
              v-model="loginForm.password"
              name="password"
              type="password"
              required
              class="form-input rounded-b-md"
              placeholder="Password"
            />
          </div>
        </div>

        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <input
              id="remember_me"
              v-model="loginForm.remember"
              name="remember_me"
              type="checkbox"
              class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
            />
            <label for="remember_me" class="ml-2 block text-sm text-gray-900">
              Remember me
            </label>
          </div>
        </div>

        <div v-if="loginError" class="text-red-600 text-sm text-center">
          {{ loginError }}
        </div>

        <div>
          <button
            type="submit"
            :disabled="loginLoading"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
          >
            <span v-if="loginLoading">Signing in...</span>
            <span v-else>Sign in</span>
          </button>
        </div>

        <div class="text-center">
          <Link href="/register" class="text-blue-600 hover:text-blue-500">
            Don't have an account? Register
          </Link>
        </div>
      </form>

      <!-- OTP Form -->
      <form v-else class="mt-8 space-y-6" @submit.prevent="submitOtp">
        <div class="rounded-md shadow-sm">
          <div>
            <label for="otp" class="sr-only">OTP Code</label>
            <input
              id="otp"
              v-model="otpForm.otp"
              name="otp"
              type="text"
              maxlength="6"
              required
              class="form-input text-center text-2xl tracking-widest"
              placeholder="000000"
            />
          </div>
        </div>

        <!-- OTP Status Information -->
        <div
          v-if="authStore.otpStatus.has_valid_otp"
          class="text-sm text-gray-600 text-center"
        >
          <p>
            OTP expires in
            {{ formatTime(authStore.otpStatus.otp_remaining_time_seconds) }}
          </p>
          <p>
            {{ authStore.otpStatus.remaining_requests }} of
            {{ authStore.otpStatus.max_requests }} requests remaining
          </p>
        </div>

        <div v-if="otpError" class="text-red-600 text-sm text-center">
          {{ otpError }}
        </div>

        <!-- Cooldown and Request Limit Messages -->
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

        <div>
          <button
            type="submit"
            :disabled="otpLoading"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
          >
            <span v-if="otpLoading">Verifying...</span>
            <span v-else>Verify OTP</span>
          </button>
        </div>

        <div class="text-center space-y-2">
          <button
            type="button"
            @click="resendOtp"
            :disabled="resendLoading || !authStore.canResendOtp"
            class="text-blue-600 hover:text-blue-500 text-sm disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="resendLoading">Sending...</span>
            <span v-else-if="!authStore.canResendOtp">
              {{
                authStore.cooldownMessage ||
                authStore.requestLimitMessage ||
                'Resend OTP'
              }}
            </span>
            <span v-else>Resend OTP</span>
          </button>
          <div>
            <button
              type="button"
              @click="backToLogin"
              class="text-gray-600 hover:text-gray-500 text-sm"
            >
              Back to login
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
  import { ref, computed } from 'vue';
  import { Link, router } from '@inertiajs/vue3';
  import { useAuthStore } from '@/stores/auth';
  import { useToastStore } from '@/stores/toast';

  const authStore = useAuthStore();
  const toastStore = useToastStore();

  const loginForm = ref({
    email: '',
    password: '',
    remember: false,
  });

  const otpForm = ref({
    otp: '',
  });

  const loginLoading = ref(false);
  const otpLoading = ref(false);
  const resendLoading = ref(false);
  const loginError = ref('');
  const otpError = ref('');

  const showOtpForm = computed(() => authStore.requiresOtp);

  const submitLogin = async () => {
    loginLoading.value = true;
    loginError.value = '';

    try {
      const result = await authStore.login(loginForm.value);

      if (result.success) {
        if (result.requiresOtp) {
          // OTP form will be shown automatically
        } else {
          // Redirect to dashboard
          router.visit('/dashboard');
        }
      } else {
        loginError.value = result.message;
      }
    } catch (error) {
      loginError.value = 'An unexpected error occurred';
    } finally {
      loginLoading.value = false;
    }
  };

  const submitOtp = async () => {
    otpLoading.value = true;
    otpError.value = '';

    try {
      const result = await authStore.verifyOtp(otpForm.value.otp);

      if (result.success) {
        // Redirect to dashboard
        router.visit('/dashboard');
      } else {
        otpError.value = result.message;
      }
    } catch (error) {
      otpError.value = 'An unexpected error occurred';
    } finally {
      otpLoading.value = false;
    }
  };

  const backToLogin = () => {
    authStore.requiresOtp = false;
    authStore.otpEmail = '';
    otpForm.value.otp = '';
    otpError.value = '';
  };

  const formatTime = seconds => {
    if (!seconds || seconds <= 0) return '0:00';
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = seconds % 60;
    return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
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
</script>
