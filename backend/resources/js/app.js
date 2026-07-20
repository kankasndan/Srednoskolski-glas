const btn = document.getElementById("userMenuBtn");
        const menu = document.getElementById("userMenu");

        btn.addEventListener("click", () => {
            menu.classList.toggle("hidden");
        });

        document.addEventListener("click", (e) => {
            if (!btn.contains(e.target) && !menu.contains(e.target)) {
                menu.classList.add("hidden");
            }
        });

        const ROW_BASE =
            "flex h-10 w-[240px] cursor-pointer items-center gap-3 rounded-[10px] border border-[#CCCCCC] px-4 py-2 text-left font-medium leading-none text-[14px] text-[#595959] transition-colors no-underline";

        const NAV_SECTIONS = [{
                title: "Модерација",
                items: [{
                        key: "nav:reports",
                        label: "Пријави",
                        badge: 18,
                        route: "reports"
                    },
                    {
                        key: "nav:sanctions",
                        label: "Санкции",
                        route: "sanctions"
                    },
                    {
                        key: "nav:appeals",
                        label: "Жалби",
                        badge: 3,
                        route: "appeals"
                    },
                ],
            },
            {
                title: "Заедница",
                items: [{
                        key: "nav:users",
                        label: "Корисници",
                        route: "users"
                    },
                    {
                        key: "nav:forums",
                        label: "Форуми",
                        route: "forums"
                    },
                    {
                        key: "nav:contact",
                        label: "Контакт кутија",
                        route: "contact"
                    },
                    {
                        key: "nav:broadcasts",
                        label: "Известувања",
                        route: "broadcasts"
                    },
                ],
            },
            {
                title: "Систем",
                items: [{
                        key: "nav:roles",
                        label: "Улоги и пермисии",
                        route: "roles"
                    },
                    {
                        key: "nav:settings",
                        label: "Поставки",
                        route: "settings"
                    },
                ],
            },
        ];

        // Determine active key from the current URL path (works after redirect/reload)
        function getActiveKeyFromPath() {
            const path = window.location.pathname.replace(/\/+$/, ""); // strip trailing slash
            const segment = path.split("/").pop();
            for (const section of NAV_SECTIONS) {
                for (const item of section.items) {
                    if (item.route === segment) return item.key;
                }
            }
            return null;
        }

        const selectedKey = getActiveKeyFromPath();

        function buildRow(item) {
            const active = item.key === selectedKey;

            const row = document.createElement("a");
            row.href = `/admin/${item.route}`; // adjust base path as needed for your Laravel routes
            row.className = ROW_BASE + (active ? " border-transparent bg-[#582FF5] font-bold text-white" : "");
            row.dataset.key = item.key;

            const radioOuter = document.createElement("span");
            radioOuter.className =
                "flex size-4 shrink-0 items-center justify-center rounded-full border-2 " +
                (active ? "border-white" : "border-[#000000]");

            if (active) {
                const dot = document.createElement("span");
                dot.className = "block size-[7px] rounded-full bg-white";
                radioOuter.appendChild(dot);
            }

            const labelSpan = document.createElement("span");
            labelSpan.className = "flex-1";
            labelSpan.textContent = item.label;

            row.appendChild(radioOuter);
            row.appendChild(labelSpan);

            if (item.badge !== undefined) {
                const badgeSpan = document.createElement("span");
                badgeSpan.className =
                    "ml-auto rounded-full px-2 py-[1px] text-[11px] font-bold " +
                    (active ? "bg-white text-[#582FF5]" : "bg-[#F88DD5] text-white");
                badgeSpan.textContent = item.badge;
                row.appendChild(badgeSpan);
            }

            return row;
        }

        function render() {
            const sidebar = document.getElementById("sidebar");
            sidebar.innerHTML = "";

            NAV_SECTIONS.forEach((section) => {
                const heading = document.createElement("div");
                heading.className = "px-1 pt-4 pb-1 text-[12px] font-bold uppercase tracking-wide text-[#9598A6]";
                heading.textContent = section.title;
                sidebar.appendChild(heading);

                section.items.forEach((item) => {
                    sidebar.appendChild(buildRow(item));
                });
            });
        }

        render();