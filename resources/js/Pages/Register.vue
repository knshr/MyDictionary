<template>
  <div
    class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8"
  >
    <div class="max-w-md w-full space-y-8">
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          {{ showOtpForm ? 'Verify Your Email' : 'Create your account' }}
        </h2>
        <p v-if="showOtpForm" class="mt-2 text-center text-sm text-gray-600">
          We've sent a verification code to {{ authStore.otpEmail }}
        </p>
      </div>

      <!-- Registration Form -->
      <form
        v-if="!showOtpForm"
        class="mt-8 space-y-6"
        @submit.prevent="submitRegister"
      >
        <div class="rounded-md shadow-sm -space-y-px">
          <div>
            <label for="name" class="sr-only">Name</label>
            <input
              id="name"
              v-model="registerForm.name"
              name="name"
              type="text"
              required
              class="form-input rounded-t-md"
              placeholder="Full name"
            />
          </div>
          <div>
            <label for="email" class="sr-only">Email address</label>
            <input
              id="email"
              v-model="registerForm.email"
              name="email"
              type="email"
              required
              class="form-input"
              placeholder="Email address"
            />
          </div>
          <div>
            <label for="password" class="sr-only">Password</label>
            <input
              id="password"
              v-model="registerForm.password"
              name="password"
              type="password"
              required
              minlength="8"
              class="form-input"
              placeholder="Password (min 8 characters)"
            />
          </div>
          <div>
            <label for="password_confirmation" class="sr-only"
              >Confirm Password</label
            >
            <input
              id="password_confirmation"
              v-model="registerForm.password_confirmation"
              name="password_confirmation"
              type="password"
              required
              class="form-input rounded-b-md"
              placeholder="Confirm password"
            />
          </div>
        </div>

        <div v-if="registerError" class="text-red-600 text-sm text-center">
          {{ registerError }}
        </div>

        <div>
          <button
            type="submit"
            :disabled="registerLoading"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
          >
            <span v-if="registerLoading">Creating account...</span>
            <span v-else>Create account</span>
          </button>
        </div>

        <div class="text-center">
          <Link href="/login" class="text-blue-600 hover:text-blue-500">
            Already have an account? Sign in
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
            <span v-else>Verify Email</span>
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
              @click="backToRegister"
              class="text-gray-600 hover:text-gray-500 text-sm"
            >
              Back to registration
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

  const registerForm = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
  });

  const otpForm = ref({
    otp: '',
  });

  const registerLoading = ref(false);
  const otpLoading = ref(false);
  const resendLoading = ref(false);
  const registerError = ref('');
  const otpError = ref('');

  const showOtpForm = computed(() => authStore.requiresOtp);

  const submitRegister = async () => {
    registerLoading.value = true;
    registerError.value = '';

    // Validate password confirmation
    if (
      registerForm.value.password !== registerForm.value.password_confirmation
    ) {
      registerError.value = 'Passwords do not match';
      registerLoading.value = false;
      return;
    }

    // Validate password length
    if (registerForm.value.password.length < 8) {
      registerError.value = 'Password must be at least 8 characters long';
      registerLoading.value = false;
      return;
    }

    try {
      const result = await authStore.register(registerForm.value);

      if (result.success) {
        if (result.requiresOtp) {
          // OTP form will be shown automatically
        } else {
          // Redirect to dashboard
          router.visit('/dashboard');
        }
      } else {
        registerError.value = result.message;
      }
    } catch (error) {
      registerError.value = 'An unexpected error occurred';
    } finally {
      registerLoading.value = false;
    }
  };

  const submitOtp = async () => {
    otpLoading.value = true;
    otpError.value = '';

    try {
      const result = await authStore.verifyRegistrationOtp(otpForm.value.otp);

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

  const backToRegister = () => {
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
      const result = await authStore.resendOtp('register');
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
