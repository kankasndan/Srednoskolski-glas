// =========================
// User menu dropdown
// =========================

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

// =========================
// Sidebar styling logic
// =========================

// Base row classes (same as your ROW_BASE)
const ROW_BASE =
    "flex h-10 w-[240px] cursor-pointer items-center gap-3 rounded-[10px] border border-[#CCCCCC] px-4 py-2 text-left font-medium leading-none text-[14px] text-[#595959] transition-colors no-underline";

// Determine active key from current path and Blade-provided data-nav-key
function getActiveKeyFromPath(links) {
    const path = window.location.pathname.replace(/\/+$/, "");

    // Try to match by href first
    for (const link of links) {
        const href = link.getAttribute("href");
        if (!href) continue;

        const url = new URL(href, window.location.origin);
        const linkPath = url.pathname.replace(/\/+$/, "");

        if (path === linkPath || path.startsWith(linkPath + "/")) {
            return link.dataset.navKey || null;
        }
    }

    return null;
}

// Apply styling to each sidebar link
function styleSidebarLinks() {
    const sidebar = document.getElementById("sidebar");
    if (!sidebar) return;

    // Links rendered by Blade; add data-nav-key="" on each
    const links = sidebar.querySelectorAll("a[data-nav-key]");
    if (!links.length) return;

    const activeKey = getActiveKeyFromPath(links);

    links.forEach((link) => {
        const key = link.dataset.navKey;
        const badgeValue = link.dataset.badge; // optional

        const isActive = key === activeKey;

        // Reset classes, then apply base + active
        link.className = ROW_BASE + (isActive ? " border-transparent bg-[#582FF5] font-bold text-white" : "");

        // Clear any existing children; we rebuild structure
        const originalLabel = link.textContent.trim();
        link.textContent = "";

        // Radio-style circle
        const radioOuter = document.createElement("span");
        radioOuter.className =
            "flex size-4 shrink-0 items-center justify-center rounded-full border-2 " +
            (isActive ? "border-white" : "border-[#000000]");

        if (isActive) {
            const dot = document.createElement("span");
            dot.className = "block size-[7px] rounded-full bg-white";
            radioOuter.appendChild(dot);
        }

        // Label text
        const labelSpan = document.createElement("span");
        labelSpan.className = "flex-1";
        labelSpan.textContent = originalLabel;

        link.appendChild(radioOuter);
        link.appendChild(labelSpan);

        // Optional badge
        if (badgeValue !== undefined && badgeValue !== "") {
            const badgeSpan = document.createElement("span");
            badgeSpan.className =
                "ml-auto rounded-full px-2 py-[1px] text-[11px] font-bold " +
                (isActive ? "bg-white text-[#582FF5]" : "bg-[#F88DD5] text-white");
            badgeSpan.textContent = badgeValue;
            link.appendChild(badgeSpan);
        }
    });
}

document.addEventListener("DOMContentLoaded", () => {
    styleSidebarLinks();
});

document.addEventListener('DOMContentLoaded', () => {
    const bellBtn = document.getElementById("bellBtn");
    const notifMenu = document.getElementById("notifMenu");

    if (bellBtn && notifMenu) {
        bellBtn.addEventListener("click", (e) => {
            e.stopPropagation();
            notifMenu.classList.toggle("hidden");
        });

        document.addEventListener("click", (e) => {
            if (!notifMenu.contains(e.target)) {
                notifMenu.classList.add("hidden");
            }
        });
    }
});