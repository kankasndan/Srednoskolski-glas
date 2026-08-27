const btn = document.getElementById("userMenuBtn");
const menu = document.getElementById("userMenu");

if (btn && menu) {
    btn.addEventListener("click", () => {
        menu.classList.toggle("hidden");
    });

    document.addEventListener("click", (e) => {
        if (!btn.contains(e.target) && !menu.contains(e.target)) {
            menu.classList.add("hidden");
        }
    });
}

const ROW_BASE =
    "flex h-10 w-[240px] cursor-pointer items-center gap-3 rounded-[10px] border border-[#CCCCCC] px-4 py-2 text-left font-medium leading-none text-[14px] text-[#595959] transition-colors no-underline";

function getActiveKeyFromPath(links) {
    const path = window.location.pathname.replace(/\/+$/, "");
    let bestKey = null;
    let bestLength = -1;

    for (const link of links) {
        const href = link.getAttribute("href");
        if (!href) continue;

        const url = new URL(href, window.location.origin);
        const linkPath = url.pathname.replace(/\/+$/, "");

        if (path === linkPath || path.startsWith(linkPath + "/")) {
            if (linkPath.length > bestLength) {
                bestKey = link.dataset.navKey || null;
                bestLength = linkPath.length;
            }
        }
    }

    return bestKey;
}

function styleSidebarLinks() {
    const sidebar = document.getElementById("sidebar");
    if (!sidebar) return;

    const links = sidebar.querySelectorAll("a[data-nav-key]");
    if (!links.length) return;

    const activeKey = getActiveKeyFromPath(links);

    links.forEach((link) => {
        const key = link.dataset.navKey;
        const badgeValue = link.dataset.badge;
        const isActive = key === activeKey;

        link.className = ROW_BASE + (isActive ? " border-transparent bg-my-purple font-bold text-white" : "");

        const originalLabel = link.textContent.trim();
        link.textContent = "";

        const radioOuter = document.createElement("span");
        radioOuter.className =
            "flex size-4 shrink-0 items-center justify-center rounded-full border-2 " +
            (isActive ? "border-white" : "border-[#000000]");

        if (isActive) {
            const dot = document.createElement("span");
            dot.className = "block size-[7px] rounded-full bg-white";
            radioOuter.appendChild(dot);
        }

        const labelSpan = document.createElement("span");
        labelSpan.className = "flex-1";
        labelSpan.textContent = originalLabel;

        link.appendChild(radioOuter);
        link.appendChild(labelSpan);

        if (badgeValue !== undefined && badgeValue !== "") {
            const badgeSpan = document.createElement("span");
            badgeSpan.className =
                "ml-auto rounded-full px-2 py-[1px] text-[11px] font-bold " +
                (isActive ? "bg-white text-my-purple" : "bg-my-pink text-white");
            badgeSpan.textContent = badgeValue;
            link.appendChild(badgeSpan);
        }
    });
}

function setupSidebarToggle() {
    const toggle = document.getElementById("sidebarToggle");
    const sidebar = document.getElementById("sidebar");
    const overlay = document.getElementById("sidebarOverlay");

    if (!toggle || !sidebar || !overlay) return;

    const close = () => {
        sidebar.classList.add("-translate-x-full");
        overlay.classList.add("hidden");
        toggle.setAttribute("aria-expanded", "false");
        toggle.setAttribute("aria-label", "Отвори мени");
    };

    const open = () => {
        sidebar.classList.remove("-translate-x-full");
        overlay.classList.remove("hidden");
        toggle.setAttribute("aria-expanded", "true");
        toggle.setAttribute("aria-label", "Затвори мени");
    };

    toggle.addEventListener("click", () => {
        if (sidebar.classList.contains("-translate-x-full")) {
            open();
        } else {
            close();
        }
    });

    overlay.addEventListener("click", close);

    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape") {
            close();
        }
    });
}

function setupConfirmDialog() {
    const dialog = document.getElementById("adminConfirmDialog");
    const message = document.getElementById("adminConfirmMessage");

    if (!dialog || !message) return;

    document.addEventListener("submit", (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) return;

        const text = form.getAttribute("data-confirm");
        if (!text || form.dataset.confirmAccepted === "1") return;

        event.preventDefault();
        message.textContent = text;

        if (typeof dialog.showModal !== "function") {
            if (window.confirm(text)) {
                form.dataset.confirmAccepted = "1";
                form.submit();
            }
            return;
        }

        dialog.showModal();
        dialog.addEventListener(
            "close",
            () => {
                if (dialog.returnValue === "confirm") {
                    form.dataset.confirmAccepted = "1";
                    form.submit();
                }
            },
            { once: true },
        );
    });
}

document.addEventListener("DOMContentLoaded", () => {
    styleSidebarLinks();
    setupSidebarToggle();
    setupConfirmDialog();

    const bellBtn = document.getElementById("bellBtn");
    const notifMenu = document.getElementById("notifMenu");

    if (bellBtn && notifMenu) {
        bellBtn.addEventListener("click", (e) => {
            e.stopPropagation();
            notifMenu.classList.toggle("hidden");
        });

        document.addEventListener("click", (e) => {
            if (!notifMenu.contains(e.target) && e.target !== bellBtn && !bellBtn.contains(e.target)) {
                notifMenu.classList.add("hidden");
            }
        });
    }
});
