import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('movementForm', (products, markup, parties, initialItems, initialValues) => {
    const rows = initialItems.map((item) => {
        const product = products.find((entry) => Number(entry.id) === Number(item.product_id));
        return {
            product_id: item.product_id,
            quantity: Number(item.quantity) || 1,
            price_unit: Number(item.price_unit) || 0,
            item_shape: item.item_shape ?? product?.item_shape ?? '',
            name: product?.name ?? '',
            barcode: product?.barcode ?? '',
            stock: Number(product?.stock) || 0,
        };
    });

    return ({
    products,
    markup,
    parties,
    type: initialValues.type || 'sale',
    partyId: initialValues.partyId || '',
    status: initialValues.status || 'confirmed',
    search: '',
    items: rows,
    discount: Number(initialValues.discount) || 0,
    submitting: false,
    error: '',
    get filteredProducts() {
        const term = this.search.trim().toLocaleLowerCase();

        if (!term) {
            return [];
        }

        return this.products
            .filter((product) => `${product.name} ${product.barcode ?? ''}`.toLocaleLowerCase().includes(term))
            .slice(0, 8);
    },
    partiesForType() {
        const requiredType = this.type === 'purchase' ? 'supplier' : 'customer';
        return this.parties.filter((party) => party.type === requiredType);
    },
    productForId(id) {
        return this.products.find((product) => Number(product.id) === Number(id));
    },
    searchKeydown(event) {
        if (event.key !== 'Enter') {
            return;
        }

        event.preventDefault();
        const term = this.search.trim().toLocaleLowerCase();
        const product = this.products.find((entry) => entry.barcode && entry.barcode.toLocaleLowerCase() === term);

        if (product) {
            this.addProduct(product);
        } else {
            this.error = 'لم يتم العثور على مادة بهذا الباركود.';
        }
    },
    changeType() {
        this.partyId = '';
        this.items.forEach((item) => {
            const product = this.productForId(item.product_id);
            if (product) {
                item.price_unit = this.type === 'purchase'
                    ? Number(product.purchase_price) || 0
                    : Number(product.sale_price ?? (product.purchase_price * (1 + this.markup / 100))) || 0;
            }
        });
    },
    get subtotal() {
        return this.items.reduce((sum, item) => sum + (Number(item.quantity) || 0) * (Number(item.price_unit) || 0), 0);
    },
    get total() {
        return Math.max(0, this.subtotal - (Number(this.discount) || 0));
    },
    addBlankItem() {
        this.items.push({ product_id: '', quantity: 1, price_unit: 0, item_shape: '', name: '', barcode: '', stock: 0 });
    },
    addProduct(product) {
        const row = this.items.find((item) => Number(item.product_id) === Number(product.id));

        if (row) {
            row.quantity = (Number(row.quantity) || 0) + 1;
        } else {
            const price = this.type === 'purchase'
                ? product.purchase_price
                : (product.sale_price ?? (product.purchase_price * (1 + this.markup / 100)));

            this.items.push({
                product_id: product.id,
                quantity: 1,
                price_unit: Number(price) || 0,
                item_shape: product.item_shape ?? '',
                name: product.name,
                barcode: product.barcode ?? '',
                stock: Number(product.stock) || 0,
            });
        }

        this.search = '';
        this.error = '';
    },
    selectProduct(row, product) {
        if (!product) {
            Object.assign(row, { product_id: '', name: '', barcode: '', stock: 0, price_unit: 0, item_shape: '' });
            return;
        }

        const price = this.type === 'purchase'
            ? product.purchase_price
            : (product.sale_price ?? (product.purchase_price * (1 + this.markup / 100)));

        Object.assign(row, {
            product_id: product.id,
            name: product.name,
            barcode: product.barcode ?? '',
            item_shape: product.item_shape ?? '',
            stock: Number(product.stock) || 0,
            price_unit: Number(price) || 0,
        });
        this.search = '';
    },
    removeItem(index) {
        this.items.splice(index, 1);
    },
    productForRow(row) {
        return this.products.find((product) => Number(product.id) === Number(row.product_id));
    },
    submitForm(event) {
        this.error = '';

        if (!this.items.length || this.items.some((item) => !item.product_id || Number(item.quantity) <= 0 || Number(item.price_unit) < 0)) {
            this.error = 'أضف مادة واحدة على الأقل، وتأكد من اختيار المادة وإدخال كمية وسعر صالحين.';
            return;
        }

        if (Number(this.discount) < 0 || Number(this.discount) > this.subtotal) {
            this.error = 'يجب ألا يتجاوز الخصم إجمالي الفاتورة.';
            return;
        }

        if (this.type === 'sale' && this.status === 'confirmed') {
            const quantities = new Map();
            this.items.forEach((item) => quantities.set(Number(item.product_id), (quantities.get(Number(item.product_id)) || 0) + Number(item.quantity)));
            const insufficient = [...quantities.entries()].find(([id, quantity]) => {
                const product = this.products.find((entry) => Number(entry.id) === id);
                return !product || quantity > Number(product.stock);
            });

            if (insufficient) {
                this.error = 'الكمية المطلوبة تتجاوز المخزون المتاح لإحدى المواد.';
                return;
            }
        }

        this.submitting = true;
        event.target.submit();
    },
    });
});

Alpine.start();

const accentThemes = new Set(['default', 'green', 'red', 'yellow']);
const root = document.documentElement;
const accentThemeButtons = document.querySelectorAll('[data-accent-theme]');
const storedAccentTheme = localStorage.getItem('warehouse-accent-theme');
const initialAccentTheme = accentThemes.has(storedAccentTheme) ? storedAccentTheme : 'default';

root.dataset.theme = initialAccentTheme;

accentThemeButtons.forEach((button) => {
    const selected = button.dataset.accentTheme === initialAccentTheme;
    button.classList.toggle('active', selected);
    button.setAttribute('aria-pressed', String(selected));

    button.addEventListener('click', () => {
        const selectedTheme = button.dataset.accentTheme;

        if (!accentThemes.has(selectedTheme)) {
            return;
        }

        root.dataset.theme = selectedTheme;
        localStorage.setItem('warehouse-accent-theme', selectedTheme);

        accentThemeButtons.forEach((themeButton) => {
            const isSelected = themeButton.dataset.accentTheme === selectedTheme;
            themeButton.classList.toggle('active', isSelected);
            themeButton.setAttribute('aria-pressed', String(isSelected));
        });
    });
});

const themeToggle = document.querySelector('[data-theme-toggle]');

if (themeToggle) {
    const root = document.documentElement;
    const darkIcon = themeToggle.querySelector('[data-theme-icon="moon"]');
    const lightIcon = themeToggle.querySelector('[data-theme-icon="sun"]');
    const label = themeToggle.querySelector('[data-theme-label]');

    const updateThemeToggle = () => {
        const isDark = root.classList.contains('dark');
        themeToggle.setAttribute('aria-pressed', String(isDark));
        themeToggle.setAttribute('aria-label', isDark ? 'تفعيل الوضع الفاتح' : 'تفعيل الوضع الداكن');
        label.textContent = isDark ? 'الوضع الفاتح' : 'الوضع الداكن';
        darkIcon.classList.toggle('hidden', isDark);
        lightIcon.classList.toggle('hidden', !isDark);
    };

    updateThemeToggle();

    themeToggle.addEventListener('click', () => {
        const isDark = root.classList.toggle('dark');
        localStorage.setItem('warehouse-theme', isDark ? 'dark' : 'light');
        updateThemeToggle();
    });
}
