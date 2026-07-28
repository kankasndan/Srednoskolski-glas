"use client";

import { useState } from "react";
import ConfirmDialog from "@/components/ConfirmDialog";
import EditThreadDialog from "@/components/EditThreadDialog";
import InfoDialog from "@/components/InfoDialog";
import ReportDialog from "@/components/ReportDialog";
import ThreeDotsMenu from "@/components/ThreeDotsMenu";

export default function ThreadActionsMenu({ thread, isOwner }) {
  // TODO: backend-ot seushte ne vrakja dali si avtor, pa go birame sluchajno za
  // da mozat da se vidat dvete varijanti na menito.
  const [randomlyOwner] = useState(() => Math.random() < 0.5);
  const showOwnerActions = isOwner ?? randomlyOwner;

  const [editing, setEditing] = useState(false);
  const [confirmingDelete, setConfirmingDelete] = useState(false);
  const [deleted, setDeleted] = useState(false);
  const [reporting, setReporting] = useState(false);
  const [reported, setReported] = useState(false);

  const items = showOwnerActions
    ? [
        { label: "Измени", onSelect: () => setEditing(true) },
        { label: "Избриши", onSelect: () => setConfirmingDelete(true) },
      ]
    : [{ label: "Пријави", onSelect: () => setReporting(true) }];

  return (
    <>
      <ThreeDotsMenu items={items} />

      {/* Samo dodeka se otvoreni, za da se resetira formata sekoj pat. */}
      {editing && (
        <EditThreadDialog
          open
          thread={thread}
          onClose={() => setEditing(false)}
          onSave={() => setEditing(false)}
        />
      )}

      {reporting && (
        <ReportDialog
          open
          onClose={() => setReporting(false)}
          onSubmit={() => {
            setReporting(false);
            setReported(true);
          }}
        />
      )}

      <ConfirmDialog
        open={confirmingDelete}
        title="Дали си сигурен дека сакаш да ја избришеш оваа дискусија?"
        confirmLabel="Избриши"
        onCancel={() => setConfirmingDelete(false)}
        onConfirm={() => {
          setConfirmingDelete(false);
          setDeleted(true);
        }}
      />

      <InfoDialog
        open={deleted}
        title="Дискусијата беше успешно избришана."
        message="Корисниците сè уште може да ги гледаат коментарите."
        onClose={() => setDeleted(false)}
      />

      <InfoDialog
        open={reported}
        title="Пријавата беше успешно поднесена и испратена до админот."
        onClose={() => setReported(false)}
      />
    </>
  );
}
