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

    const cepInput = document.querySelector('#cep[data-mask="cep"]');

    if (!cepInput) {
        return;
    }

    const fields = {
        rua: document.getElementById('rua'),
        bairro: document.getElementById('bairro'),
        cidade: document.getElementById('cidade'),
        estado: document.getElementById('estado'),
        pais: document.getElementById('pais'),
        numero: document.getElementById('numero'),
    };
    const status = document.getElementById('cep-status');
    let timer;
    let controller;
    let lastCep = '';

    const setStatus = (message, state = '') => {
        if (!status) return;
        status.textContent = message;
        status.dataset.state = state;
    };

    const fillAddress = (address) => {
        const values = {
            rua: address.logradouro,
            bairro: address.bairro,
            cidade: address.localidade,
            estado: address.uf,
            pais: 'Brasil',
        };

        Object.entries(values).forEach(([name, value]) => {
            if (fields[name] && value) {
                fields[name].value = value;
            }
        });
    };

    const lookupCep = async (focusNumber = false) => {
        const cep = onlyDigits(cepInput.value, 8);

        if (cep.length !== 8) {
            lastCep = '';
            setStatus(cep.length ? 'Complete os 8 números do CEP.' : 'Informe os 8 números do CEP.');
            return;
        }

        if (cep === lastCep) {
            return;
        }

        controller?.abort();
        const requestController = new AbortController();
        controller = requestController;
        cepInput.classList.add('is-loading');
        cepInput.setAttribute('aria-busy', 'true');
        setStatus('Buscando endereço…', 'loading');

        try {
            const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`, {
                signal: requestController.signal,
                headers: { Accept: 'application/json' },
            });

            if (!response.ok) throw new Error('request_failed');

            const address = await response.json();
            if (address.erro) {
                lastCep = '';
                setStatus('CEP não encontrado. Confira ou preencha o endereço manualmente.', 'error');
                return;
            }

            fillAddress(address);
            lastCep = cep;
            setStatus('Endereço encontrado e preenchido.', 'success');
            if (focusNumber) fields.numero?.focus();
        } catch (error) {
            if (error.name !== 'AbortError') {
                lastCep = '';
                setStatus('Não foi possível consultar agora. Preencha o endereço manualmente.', 'error');
            }
        } finally {
            if (controller === requestController) {
                cepInput.classList.remove('is-loading');
                cepInput.removeAttribute('aria-busy');
            }
        }
    };

    cepInput.addEventListener('input', () => {
        window.clearTimeout(timer);
        timer = window.setTimeout(() => lookupCep(false), 450);
    });

    cepInput.addEventListener('blur', () => {
        window.clearTimeout(timer);
        lookupCep(true);
    });
})();
