// Controle da UX de detalhes e modal na vitrine de produtos.
(function () {
    var config = window.JTECH_CART_CONFIG || {};
    var storageKey = config.storageKey || 'jtech_cart_guest';

    var toggleButtons = document.querySelectorAll('.js-toggle-desc');
    toggleButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            var targetId = button.getAttribute('data-target');
            var descricao = document.getElementById(targetId);
            if (!descricao) return;

            var oculto = descricao.classList.toggle('is-hidden');
            button.setAttribute('aria-expanded', String(!oculto));
        });
    });

    var modal = document.getElementById('desc-modal');
    var modalTitle = document.getElementById('desc-modal-title');
    var modalBody = document.getElementById('desc-modal-body');
    if (!modal || !modalTitle || !modalBody) return;

    var openButtons = document.querySelectorAll('.js-open-modal');
    openButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            var title = button.getAttribute('data-title') || 'Descricao do produto';
            var fullDesc = button.getAttribute('data-full-desc') || 'Sem descricao detalhada.';

            modalTitle.textContent = title;
            modalBody.textContent = fullDesc;
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
        });
    });

    function closeModal() {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
    }

    var closeButtons = document.querySelectorAll('.js-close-modal');
    closeButtons.forEach(function (button) {
        button.addEventListener('click', closeModal);
    });

    modal.addEventListener('click', function (event) {
        if (event.target === modal) {
            closeModal();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal.classList.contains('is-open')) {
            closeModal();
        }
    });

    var cartFab = document.getElementById('cart-fab');
    var cartPanel = document.getElementById('cart-panel');
    var cartBadge = document.getElementById('cart-badge');
    var cartList = document.getElementById('cart-list');
    var cartTotal = document.getElementById('cart-total');
    var cartClose = document.getElementById('cart-close');
    var cartClear = document.getElementById('cart-clear');
    var cartCheckout = document.getElementById('cart-checkout');

    if (!cartFab || !cartPanel || !cartBadge || !cartList || !cartTotal) return;

    function readCart() {
        try {
            var raw = localStorage.getItem(storageKey);
            if (!raw) return [];
            var parsed = JSON.parse(raw);
            return Array.isArray(parsed) ? parsed : [];
        } catch (err) {
            return [];
        }
    }

    function writeCart(cart) {
        localStorage.setItem(storageKey, JSON.stringify(cart));
    }

    function toMoney(value) {
        return 'R$ ' + Number(value || 0).toFixed(2).replace('.', ',');
    }

    function cartTotalValue(cart) {
        return cart.reduce(function (sum, item) {
            return sum + (Number(item.price) * Number(item.quantity));
        }, 0);
    }

    function cartTotalItems(cart) {
        return cart.reduce(function (sum, item) {
            return sum + Number(item.quantity || 0);
        }, 0);
    }

    function removeItemById(id) {
        var cart = readCart().filter(function (item) {
            return item.lineId !== id;
        });
        writeCart(cart);
        renderCart();
    }

    function renderCart() {
        var cart = readCart();
        cartBadge.textContent = String(cartTotalItems(cart));
        cartTotal.textContent = toMoney(cartTotalValue(cart));

        if (cart.length === 0) {
            cartList.innerHTML = '<p class="cart-empty">Seu carrinho esta vazio.</p>';
            return;
        }

        cartList.innerHTML = cart.map(function (item) {
            return (
                '<article class="cart-item">' +
                    '<img src="' + (item.image || '') + '" alt="Produto">' +
                    '<div>' +
                        '<h4>' + (item.name || 'Produto') + '</h4>' +
                        '<p>Cor: ' + (item.color || '-') + ' · Qtd: ' + Number(item.quantity || 1) + '</p>' +
                        '<p>' + toMoney(Number(item.price) * Number(item.quantity)) + '</p>' +
                    '</div>' +
                    '<button class="cart-remove" type="button" data-remove-id="' + item.lineId + '">Remover</button>' +
                '</article>'
            );
        }).join('');

        cartList.querySelectorAll('[data-remove-id]').forEach(function (button) {
            button.addEventListener('click', function () {
                removeItemById(button.getAttribute('data-remove-id'));
            });
        });
    }

    function openCart() {
        cartPanel.classList.add('is-open');
        cartPanel.setAttribute('aria-hidden', 'false');
        cartFab.setAttribute('aria-expanded', 'true');
    }

    function closeCart() {
        cartPanel.classList.remove('is-open');
        cartPanel.setAttribute('aria-hidden', 'true');
        cartFab.setAttribute('aria-expanded', 'false');
    }

    cartFab.addEventListener('click', function () {
        if (cartPanel.classList.contains('is-open')) {
            closeCart();
        } else {
            openCart();
        }
    });

    if (cartClose) {
        cartClose.addEventListener('click', closeCart);
    }

    if (cartClear) {
        cartClear.addEventListener('click', function () {
            writeCart([]);
            renderCart();
        });
    }

    if (cartCheckout) {
        cartCheckout.addEventListener('click', function () {
            var cart = readCart();
            if (cart.length === 0) {
                alert('Seu carrinho esta vazio.');
                return;
            }

            // DESTAQUE: redireciona para o checkout real com os itens do carrinho.
            window.location.href = 'checkout.php';
        });
    }

    var addButtons = document.querySelectorAll('.js-add-cart');
    addButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            var card = button.closest('.produto-card');
            if (!card) return;

            var qtyInput = card.querySelector('.js-prod-qtd');
            var colorSelect = card.querySelector('.js-prod-cor');

            var quantity = Number(qtyInput ? qtyInput.value : 1);
            if (!Number.isFinite(quantity) || quantity < 1) {
                quantity = 1;
            }

            var color = colorSelect ? colorSelect.value : 'Preto';
            var name = button.getAttribute('data-name') || 'Produto';
            var price = Number(button.getAttribute('data-price') || 0);
            var image = button.getAttribute('data-image') || '';

            var cart = readCart();
            cart.push({
                lineId: 'l_' + Date.now() + '_' + Math.floor(Math.random() * 10000),
                name: name,
                price: price,
                image: image,
                color: color,
                quantity: quantity
            });

            writeCart(cart);
            renderCart();
            openCart();
        });
    });

    renderCart();
})();
