/**
 * Return type for useInitials composable.
 */
export type UseInitialsReturn = {
    /** Function to get initials from a full name */
    getInitials: (fullName?: string) => string;
};

/**
 * Get initials from a full name.
 *
 * @param fullName The full name to extract initials from
 * @returns The initials (e.g., "JD" for "John Doe")
 */
export function getInitials(fullName?: string): string {
    if (!fullName) {
        return '';
    }

    const names = fullName.trim().split(' ');

    if (names.length === 0) {
        return '';
    }
    if (names.length === 1) {
        return names[0].charAt(0).toUpperCase();
    }

    return `${names[0].charAt(0)}${names[names.length - 1].charAt(0)}`.toUpperCase();
}

/**
 * Composable for generating user initials.
 *
 * @returns UseInitialsReturn Object containing getInitials function
 */
export function useInitials(): UseInitialsReturn {
    return { getInitials };
}
