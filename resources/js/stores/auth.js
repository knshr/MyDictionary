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

  // OTP state persistence
  const OTP_STORAGE_KEY = 'pending_otp_state';

  const saveOtpState = (email, type = 'login') => {
    const otpState = {
      email,
      type,
      timestamp: Date.now(),
      requiresOtp: true,
    };
    localStorage.setItem(OTP_STORAGE_KEY, JSON.stringify(otpState));
  };

  const loadOtpState = () => {
    try {
      const stored = localStorage.getItem(OTP_STORAGE_KEY);
      if (!stored) return null;

      const otpState = JSON.parse(stored);
      const now = Date.now();
      const otpExpiryTime = 10 * 60 * 1000; // 10 minutes in milliseconds

      // Check if OTP state is still valid (not expired)
      if (now - otpState.timestamp > otpExpiryTime) {
        clearOtpState();
        return null;
      }

      return otpState;
    } catch (error) {
      console.error('Error loading OTP state:', error);
      clearOtpState();
      return null;
    }
  };

  const clearOtpState = () => {
    localStorage.removeItem(OTP_STORAGE_KEY);
  };

  const restoreOtpState = async () => {
    const otpState = loadOtpState();
    console.log('Loading OTP state:', otpState);
    if (otpState) {
      console.log('Setting requiresOtp to true');
      requiresOtp.value = true;
      otpEmail.value = otpState.email;
      console.log('OTP email set to:', otpEmail.value);

      // Try to get current OTP status from server
      try {
        console.log('Fetching OTP status from server...');
        await getOtpStatus(otpState.type);
        console.log('OTP status fetched successfully');
        return true;
      } catch (error) {
        console.error('Error restoring OTP status:', error);
        // If we can't get status from server, clear the state
        clearOtpState();
        requiresOtp.value = false;
        otpEmail.value = '';
        return false;
      }
    }
    console.log('No OTP state found to restore');
    return false;
  };

  // Set up axios defaults for Sanctum token authentication
  if (token.value) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;
  }

  const setUser = userData => {
    console.log('Setting user:', userData);
    user.value = userData;
    console.log('User set, current user value:', user.value);
    console.log('Current token value:', token.value);
    console.log('isAuthenticated computed value:', isAuthenticated.value);
  };

  const setToken = authToken => {
    console.log('Setting token:', authToken ? 'Token received' : 'No token');
    token.value = authToken;
    localStorage.setItem('auth_token', authToken);
    axios.defaults.headers.common['Authorization'] = `Bearer ${authToken}`;
    console.log('Token set, current user value:', user.value);
    console.log('Current token value:', token.value);
    console.log('isAuthenticated computed value:', isAuthenticated.value);
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

      if (response.data.success) {
        // Check if the response indicates OTP is required
        if (response.data.data.requires_otp) {
          requiresOtp.value = true;
          otpEmail.value = credentials.email;

          // Save OTP state to localStorage
          saveOtpState(credentials.email, 'login');

          // Update OTP status when OTP is required
          await getOtpStatus('login');

          return {
            success: true,
            requiresOtp: true,
            message: response.data.message,
          };
        } else {
          // Login successful without OTP, redirect to dashboard
          window.location.href = '/dashboard';
          return { success: true, requiresOtp: false };
        }
      }

      return { success: false, message: 'Login failed' };
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Login failed',
        errors: error.response?.data?.errors || {},
      };
    }
  };

  const verifyOtp = async otp => {
    // This method is no longer used - OTP verification is now handled by Inertia forms
    console.log(
      'verifyOtp method is deprecated - use Inertia form handling instead'
    );
    return {
      success: false,
      message: 'Use Inertia form handling for OTP verification',
    };
  };

  const register = async userData => {
    try {
      const response = await axios.post('/api/auth/register', userData);

      if (response.data.success) {
        // Check if the response indicates OTP is required
        if (response.data.data.requires_otp) {
          requiresOtp.value = true;
          otpEmail.value = userData.email;

          // Save OTP state to localStorage
          saveOtpState(userData.email, 'register');

          // Update OTP status when OTP is required
          await getOtpStatus('register');

          return {
            success: true,
            requiresOtp: true,
            message: response.data.message,
          };
        } else {
          // Registration successful without OTP, redirect to dashboard
          window.location.href = '/dashboard';
          return { success: true, requiresOtp: false };
        }
      }

      return { success: false, message: 'Registration failed' };
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

      if (response.data.success) {
        // Store the token and user data
        if (response.data.data.token) {
          setToken(response.data.data.token);
          setUser(response.data.data.user);
        }

        // Clear OTP state
        requiresOtp.value = false;
        otpEmail.value = '';
        clearOtpState();

        // Use Inertia router instead of window.location to preserve state
        return { success: true, message: 'OTP verified successfully.' };
      }

      return { success: false, message: 'OTP verification failed' };
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

      return {
        success: true,
        message: response.data.message || 'OTP sent successfully.',
      };
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
      // Use API logout to revoke token
      await axios.post('/api/auth/logout');
    } catch (error) {
      console.error('Logout error:', error);
    } finally {
      clearUser();
      requiresOtp.value = false;
      otpEmail.value = '';
      clearOtpState();
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
    saveOtpState,
    loadOtpState,
    clearOtpState,
    restoreOtpState,
    login,
    verifyOtp,
    register,
    verifyRegistrationOtp,
    resendOtp,
    logout,
    getCurrentUser,
  };
});
