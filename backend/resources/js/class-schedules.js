const scheduleRows = document.querySelector("#schedule-rows");
const scheduleTemplate = document.querySelector("#schedule-row-template");
const addScheduleButton = document.querySelector("#add-schedule");

if (scheduleRows && scheduleTemplate && addScheduleButton) {
    const updateScheduleRows = () => {
        const rows = [...scheduleRows.querySelectorAll(".schedule-row")];

        rows.forEach((row, index) => {
            row.querySelectorAll("[data-field]").forEach((field) => {
                const key = field.dataset.field;
                field.name = `schedules[${index}][${key}]`;
                field.id = `schedules-${index}-${key.replaceAll("_", "-")}`;
                field.parentElement.querySelector("label").htmlFor = field.id;
            });

            row.querySelector(".schedule-remove-button").disabled = rows.length === 1;
        });
    };

    addScheduleButton.addEventListener("click", () => {
        if (scheduleRows.querySelectorAll(".schedule-row").length >= 20) {
            return;
        }

        scheduleRows.append(scheduleTemplate.content.cloneNode(true));
        updateScheduleRows();
        scheduleRows.lastElementChild.querySelector("select").focus();
    });

    scheduleRows.addEventListener("click", (event) => {
        const removeButton = event.target.closest(".schedule-remove-button");

        if (!removeButton || scheduleRows.querySelectorAll(".schedule-row").length === 1) {
            return;
        }

        removeButton.closest(".schedule-row").remove();
        updateScheduleRows();
    });

    updateScheduleRows();
}

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
