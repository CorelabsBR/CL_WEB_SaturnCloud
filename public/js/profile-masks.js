(() => {
    const onlyDigits = (value, maxLength) => value.replace(/\D+/g, '').slice(0, maxLength);

    const masks = {
        cep(value) {
            const digits = onlyDigits(value, 8);

            if (digits.length <= 5) {
                return digits;
            }

            return `${digits.slice(0, 5)}-${digits.slice(5)}`;
        },

        phone(value) {
            const digits = onlyDigits(value, 11);

            if (digits.length <= 2) {
                return digits;
            }

            if (digits.length <= 6) {
                return `(${digits.slice(0, 2)}) ${digits.slice(2)}`;
            }

            if (digits.length <= 10) {
                return `(${digits.slice(0, 2)}) ${digits.slice(2, 6)}-${digits.slice(6)}`;
            }

            return `(${digits.slice(0, 2)}) ${digits.slice(2, 7)}-${digits.slice(7)}`;
        },

        document(value) {
            const digits = onlyDigits(value, 14);

            if (digits.length <= 3) {
                return digits;
            }

            if (digits.length <= 6) {
                return `${digits.slice(0, 3)}.${digits.slice(3)}`;
            }

            if (digits.length <= 9) {
                return `${digits.slice(0, 3)}.${digits.slice(3, 6)}.${digits.slice(6)}`;
            }

            if (digits.length <= 11) {
                return `${digits.slice(0, 3)}.${digits.slice(3, 6)}.${digits.slice(6, 9)}-${digits.slice(9)}`;
            }

            return `${digits.slice(0, 2)}.${digits.slice(2, 5)}.${digits.slice(5, 8)}/${digits.slice(8, 12)}-${digits.slice(12)}`;
        },
    };

    const applyMask = (input) => {
        const mask = masks[input.dataset.mask];

        if (!mask) {
            return;
        }

        input.value = mask(input.value);
    };

    document.querySelectorAll('[data-mask]').forEach((input) => {
        applyMask(input);

        input.addEventListener('input', () => applyMask(input));
        input.addEventListener('paste', () => {
            window.setTimeout(() => applyMask(input));
        });
    });
})();
