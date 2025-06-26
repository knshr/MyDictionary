import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null);
  const token = ref(localStorage.getItem('auth_token'));
  const isAuthenticated = computed(() => !!user.value && !!token.value);
  const requiresOtp = ref(false);
  const otpEmail = ref('');

  // OTP status tracking
  const otpStatus = ref({
    remaining_requests: 5,
    max_requests: 5,
    request_window_minutes: 30,
    next_request_available_in_minutes: 0,
    resend_cooldown_remaining_seconds: 0,
    has_valid_otp: false,
    otp_remaining_time_seconds: 0,
  });

  // Set up axios defaults
  if (token.value) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;
  }

  const setUser = userData => {
    user.value = userData;
  };

  const setToken = authToken => {
    token.value = authToken;
    localStorage.setItem('auth_token', authToken);
    axios.defaults.headers.common['Authorization'] = `Bearer ${authToken}`;
  };

  const clearUser = () => {
    user.value = null;
    token.value = null;
    localStorage.removeItem('auth_token');
    delete axios.defaults.headers.common['Authorization'];
  };

  const updateOtpStatus = status => {
    otpStatus.value = { ...otpStatus.value, ...status };
  };

  const getOtpStatus = async (type = 'login') => {
    try {
      const response = await axios.post('/api/auth/otp-status', {
        email: otpEmail.value,
        type: type,
      });
      updateOtpStatus(response.data.data);
      return { success: true, data: response.data.data };
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Failed to get OTP status',
      };
    }
  };

  const canResendOtp = computed(() => {
    return (
      otpStatus.value.remaining_requests > 0 &&
      otpStatus.value.resend_cooldown_remaining_seconds <= 0
    );
  });

  const cooldownMessage = computed(() => {
    if (otpStatus.value.resend_cooldown_remaining_seconds > 0) {
      const minutes = Math.floor(
        otpStatus.value.resend_cooldown_remaining_seconds / 60
      );
      const seconds = otpStatus.value.resend_cooldown_remaining_seconds % 60;
      return `Please wait ${minutes}m ${seconds}s before requesting another OTP.`;
    }
    return '';
  });

  const requestLimitMessage = computed(() => {
    if (otpStatus.value.remaining_requests <= 0) {
      return `You have reached the maximum limit of ${otpStatus.value.max_requests} OTP requests within ${otpStatus.value.request_window_minutes} minutes. Please wait ${otpStatus.value.next_request_available_in_minutes} minutes before requesting another OTP.`;
    }
    return '';
  });

  const login = async credentials => {
    try {
      const response = await axios.post('/api/auth/login', credentials);
      const { data } = response.data;

      if (data.requires_otp) {
        requiresOtp.value = true;
        otpEmail.value = credentials.email;
        setUser(data.user);

        // Update OTP status when OTP is required
        await getOtpStatus('login');

        return { success: true, requiresOtp: true, message: data.message };
      } else {
        setUser(data.user);
        if (data.token) {
          setToken(data.token);
        }
        return { success: true, requiresOtp: false };
      }
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Login failed',
        errors: error.response?.data?.errors || {},
      };
    }
  };

  const verifyOtp = async otp => {
    try {
      const response = await axios.post('/api/auth/verify-otp', {
        email: otpEmail.value,
        otp: otp,
      });
      const { data } = response.data;

      setUser(data.user);
      if (data.token) {
        setToken(data.token);
      }
      requiresOtp.value = false;
      otpEmail.value = '';

      return { success: true, message: data.message };
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'OTP verification failed',
      };
    }
  };

  const register = async userData => {
    try {
      const response = await axios.post('/api/auth/register', userData);
      const { data } = response.data;

      if (data.requires_otp) {
        requiresOtp.value = true;
        otpEmail.value = userData.email;
        setUser(data.user);

        // Update OTP status when OTP is required
        await getOtpStatus('register');

        return { success: true, requiresOtp: true, message: data.message };
      } else {
        setUser(data.user);
        if (data.token) {
          setToken(data.token);
        }
        return { success: true, requiresOtp: false };
      }
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Registration failed',
        errors: error.response?.data?.errors || {},
      };
    }
  };

  const verifyRegistrationOtp = async otp => {
    try {
      const response = await axios.post('/api/auth/verify-registration-otp', {
        email: otpEmail.value,
        otp: otp,
      });
      const { data } = response.data;

      setUser(data.user);
      if (data.token) {
        setToken(data.token);
      }
      requiresOtp.value = false;
      otpEmail.value = '';

      return { success: true, message: data.message };
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'OTP verification failed',
      };
    }
  };

  const resendOtp = async (type = 'login') => {
    try {
      const response = await axios.post('/api/auth/resend-otp', {
        email: otpEmail.value,
        type: type,
      });

      // Update OTP status after successful resend
      await getOtpStatus(type);

      return { success: true, message: response.data.message };
    } catch (error) {
      // Update OTP status even on error to get current limits
      await getOtpStatus(type);

      return {
        success: false,
        message: error.response?.data?.message || 'Failed to resend OTP',
        errors: error.response?.data?.errors || {},
      };
    }
  };

  const logout = async () => {
    try {
      if (token.value) {
        await axios.post('/api/auth/logout');
      }
    } catch (error) {
      console.error('Logout error:', error);
    } finally {
      clearUser();
      requiresOtp.value = false;
      otpEmail.value = '';
    }
  };

  const getCurrentUser = async () => {
    try {
      const response = await axios.get('/api/auth/me');
      setUser(response.data.data.user);
      return { success: true };
    } catch (error) {
      clearUser();
      return { success: false };
    }
  };

  return {
    user,
    token,
    isAuthenticated,
    requiresOtp,
    otpEmail,
    otpStatus,
    setUser,
    setToken,
    clearUser,
    updateOtpStatus,
    getOtpStatus,
    canResendOtp,
    cooldownMessage,
    requestLimitMessage,
    login,
    verifyOtp,
    register,
    verifyRegistrationOtp,
    resendOtp,
    logout,
    getCurrentUser,
  };
});
