import { ref, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'

export interface CookieCategory {
  name: string
  description: string
  required: boolean
  default_enabled: boolean
  cookies: string[]
}

export interface CookieConsentConfig {
  enabled: boolean
  gdpr_mode: boolean
  banner: {
    title: string
    description: string
    buttons: {
      accept_all: string
      reject_all: string
      customize: string
      save_preferences: string
    }
    links: {
      privacy_policy: {
        text: string
        url: string
      }
      cookie_policy: {
        text: string
        url: string
      }
    }
  }
  modal: {
    title: string
    description: string
    buttons: {
      save: string
      cancel: string
      accept_all: string
      reject_all: string
    }
    sections: {
      essential_title: string
      essential_description: string
      optional_title: string
      optional_description: string
    }
  }
}

export interface CookieConsentState {
  hasConsent: boolean
  preferences: Record<string, boolean>
  categories: Record<string, CookieCategory>
  config: CookieConsentConfig
}

const cookieConsentState = ref<CookieConsentState>({
  hasConsent: false,
  preferences: {},
  categories: {},
  config: {
    enabled: true,
    gdpr_mode: true,
    banner: {
      title: 'We use cookies',
      description: 'We use cookies to enhance your browsing experience.',
      buttons: {
        accept_all: 'Accept All',
        reject_all: 'Reject All',
        customize: 'Customize',
        save_preferences: 'Save Preferences',
      },
      links: {
        privacy_policy: {
          text: 'Privacy Policy',
          url: '/privacy-policy',
        },
        cookie_policy: {
          text: 'Cookie Policy',
          url: '/cookie-policy',
        },
      },
    },
    modal: {
      title: 'Cookie Preferences',
      description: 'Manage your cookie preferences.',
      buttons: {
        save: 'Save Preferences',
        cancel: 'Cancel',
        accept_all: 'Accept All',
        reject_all: 'Reject All',
      },
      sections: {
        essential_title: 'Essential Cookies',
        essential_description: 'These cookies are necessary for the website to function.',
        optional_title: 'Optional Cookies',
        optional_description: 'You can choose which optional cookies to allow.',
      },
    },
  },
})

// Generate unique storage key based on environment
const getStorageKey = (): string => {
  const prefix = window.location.hostname === 'localhost' ? 'localhost' : 'production'
  return `cookie_consent_${prefix}_${window.location.port || '80'}`
}

// Load preferences from localStorage
const loadPreferences = (): void => {
  try {
    const stored = localStorage.getItem(getStorageKey())
    if (stored) {
      const parsed = JSON.parse(stored)
      cookieConsentState.value.preferences = parsed.preferences || {}
      cookieConsentState.value.hasConsent = parsed.hasConsent || false
    }
  } catch (error) {
    console.warn('Failed to load cookie preferences from localStorage:', error)
  }
}

// Save preferences to localStorage
const savePreferences = (preferences: Record<string, boolean>): void => {
  try {
    const data = {
      preferences,
      hasConsent: true,
      timestamp: new Date().toISOString(),
    }
    localStorage.setItem(getStorageKey(), JSON.stringify(data))
    cookieConsentState.value.preferences = preferences
    cookieConsentState.value.hasConsent = true
  } catch (error) {
    console.warn('Failed to save cookie preferences to localStorage:', error)
  }
}

// Check if a specific category is allowed
const isCategoryAllowed = (category: string): boolean => {
  return cookieConsentState.value.preferences[category] || false
}

// Accept all cookies
const acceptAll = async (): Promise<void> => {
  const categories = Object.keys(cookieConsentState.value.categories)
  const preferences = Object.fromEntries(
    categories.map(category => [category, true])
  )
  
  try {
    await router.post('/cookie-consent/accept-all', {}, {
      preserveState: true,
      preserveScroll: true,
    })
    savePreferences(preferences)
  } catch (error) {
    console.error('Failed to accept all cookies:', error)
    // Fallback to local storage
    savePreferences(preferences)
  }
}

// Reject all non-essential cookies
const rejectAll = async (): Promise<void> => {
  const categories = Object.keys(cookieConsentState.value.categories)
  const preferences = Object.fromEntries(
    categories.map(category => [
      category,
      category === 'essential' || cookieConsentState.value.categories[category]?.required || false
    ])
  )
  
  try {
    await router.post('/cookie-consent/reject-all', {}, {
      preserveState: true,
      preserveScroll: true,
    })
    savePreferences(preferences)
  } catch (error) {
    console.error('Failed to reject all cookies:', error)
    // Fallback to local storage
    savePreferences(preferences)
  }
}

// Update specific preferences
const updatePreferences = async (preferences: Record<string, boolean>): Promise<void> => {
  try {
    await router.post('/cookie-consent', preferences, {
      preserveState: true,
      preserveScroll: true,
    })
    savePreferences(preferences)
  } catch (error) {
    console.error('Failed to update cookie preferences:', error)
    // Fallback to local storage
    savePreferences(preferences)
  }
}

// Initialize from server data
const initializeFromServer = (serverData: CookieConsentState): void => {
  cookieConsentState.value = { ...serverData }
  
  // If we have server data, update localStorage
  if (serverData.hasConsent) {
    savePreferences(serverData.preferences)
  } else {
    // Load from localStorage as fallback
    loadPreferences()
  }
}

// Computed properties
const hasConsent = computed(() => cookieConsentState.value.hasConsent)
const preferences = computed(() => cookieConsentState.value.preferences)
const categories = computed(() => cookieConsentState.value.categories)
const config = computed(() => cookieConsentState.value.config)
const isEnabled = computed(() => cookieConsentState.value.config.enabled)

// Watch for changes and emit events
watch(hasConsent, (newValue) => {
  if (newValue) {
    // Emit custom event when consent is given
    window.dispatchEvent(new CustomEvent('cookieConsentGiven', {
      detail: { preferences: cookieConsentState.value.preferences }
    }))
  }
})

/**
 * Return type for useCookieConsent composable.
 */
export interface UseCookieConsentReturn {
    /** Whether the user has given consent */
    hasConsent: ReturnType<typeof computed<boolean>>;
    /** Current cookie preferences */
    preferences: ReturnType<typeof computed<Record<string, boolean>>>;
    /** Available cookie categories */
    categories: ReturnType<typeof computed<Record<string, CookieCategory>>>;
    /** Cookie consent configuration */
    config: ReturnType<typeof computed<CookieConsentConfig>>;
    /** Whether cookie consent is enabled */
    isEnabled: ReturnType<typeof computed<boolean>>;
    /** Accept all cookies */
    acceptAll: () => Promise<void>;
    /** Reject all non-essential cookies */
    rejectAll: () => Promise<void>;
    /** Update specific cookie preferences */
    updatePreferences: (preferences: Record<string, boolean>) => Promise<void>;
    /** Check if a category is allowed */
    isCategoryAllowed: (category: string) => boolean;
    /** Initialize from server data */
    initializeFromServer: (serverData: CookieConsentState) => void;
    /** Load preferences from localStorage */
    loadPreferences: () => void;
    /** Save preferences to localStorage */
    savePreferences: (preferences: Record<string, boolean>) => void;
    /** Raw state for advanced usage */
    cookieConsentState: typeof cookieConsentState;
}

/**
 * Composable for managing cookie consent preferences.
 *
 * @returns UseCookieConsentReturn Object containing consent state and methods
 */
export function useCookieConsent(): UseCookieConsentReturn {
  return {
    // State
    hasConsent,
    preferences,
    categories,
    config,
    isEnabled,
    
    // Methods
    acceptAll,
    rejectAll,
    updatePreferences,
    isCategoryAllowed,
    initializeFromServer,
    loadPreferences,
    savePreferences,
    
    // Raw state for advanced usage
    cookieConsentState,
  }
}
