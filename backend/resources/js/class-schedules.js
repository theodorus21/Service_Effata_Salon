const productSelect = document.getElementById("product_id");
const sectionPlaceholder = document.getElementById("section-placeholder");
const durationInfo = document.getElementById("duration-info");

const createRepeatableSection = ({ section, container, template, addButton, max, prefix }) => {
    let enabled = true;

    const rowsOf = () => [...container.querySelectorAll(".schedule-row")];

    const reindex = () => {
        const rows = rowsOf();

        rows.forEach((row, index) => {
            row.querySelectorAll("[data-field]").forEach((field) => {
                const key = field.dataset.field;
                field.name = `${prefix}[${index}][${key}]`;
                field.id = `${prefix}-${index}-${key.replaceAll("_", "-")}`;
                field.parentElement.querySelector("label").htmlFor = field.id;
                field.disabled = !enabled;
            });

            row.querySelector(".schedule-remove-button").disabled = !enabled || rows.length === 1;
        });

        addButton.disabled = !enabled || rows.length >= max;
    };

    const addRow = () => {
        if (rowsOf().length >= max) {
            return null;
        }

        container.append(template.content.cloneNode(true));
        reindex();

        return container.lastElementChild;
    };

    const setEnabled = (value) => {
        enabled = value;
        section.hidden = !value;

        if (value && rowsOf().length === 0) {
            addRow();
        }

        reindex();
    };

    addButton.addEventListener("click", () => {
        addRow()?.querySelector("[data-field]")?.focus();
    });

    container.addEventListener("click", (event) => {
        const removeButton = event.target.closest(".schedule-remove-button");

        if (!removeButton || rowsOf().length === 1) {
            return;
        }

        removeButton.closest(".schedule-row").remove();
        reindex();
    });

    reindex();

    return { setEnabled };
};

const scheduleSection = document.querySelector('[data-section="monthly"]');
const datedSection = document.querySelector('[data-section="dated"]');

const monthlySchedules = scheduleSection && createRepeatableSection({
    section: scheduleSection,
    container: document.getElementById("schedule-rows"),
    template: document.getElementById("schedule-row-template"),
    addButton: document.getElementById("add-schedule"),
    max: Number(document.getElementById("schedule-rows")?.dataset.max ?? 20),
    prefix: "schedules",
});

const datedLessons = datedSection && createRepeatableSection({
    section: datedSection,
    container: document.getElementById("lesson-rows"),
    template: document.getElementById("lesson-row-template"),
    addButton: document.getElementById("add-lesson"),
    max: Number(document.getElementById("lesson-rows")?.dataset.max ?? 50),
    prefix: "lessons",
});

const syncProductSections = () => {
    const option = productSelect?.selectedOptions[0];
    const hasProduct = Boolean(option?.value);
    const isMonthly = hasProduct && option.dataset.monthly === "1";

    if (sectionPlaceholder) {
        sectionPlaceholder.hidden = hasProduct;
    }

    if (durationInfo) {
        durationInfo.textContent = hasProduct && option.dataset.duration
            ? `Durasi per pertemuan: ${option.dataset.duration} menit (mengikuti produk).`
            : "";
    }

    monthlySchedules?.setEnabled(isMonthly);
    datedLessons?.setEnabled(hasProduct && !isMonthly);
};

productSelect?.addEventListener("change", syncProductSections);
syncProductSections();

document.querySelectorAll("[data-open-cancel-dialog]").forEach((button) => {
    button.addEventListener("click", () => {
        document.getElementById(button.dataset.openCancelDialog)?.showModal();
    });
});

document.querySelectorAll("[data-close-cancel-dialog]").forEach((button) => {
    button.addEventListener("click", () => {
        button.closest("dialog")?.close();
    });
});

document.querySelectorAll("dialog[data-open-on-load]").forEach((dialog) => {
    dialog.showModal();
});
