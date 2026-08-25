"use client";

import { useEffect, useState } from "react";
import SanctionAppealDialog from "@/components/ui/SanctionAppealDialog";
import SanctionDialog from "@/components/ui/SanctionDialog";
import SanctionReasonDialog from "@/components/ui/SanctionReasonDialog";
import { acknowledgeSanction, submitAppeal } from "@/api/sanctions";
import { useProfile } from "@/hooks/useProfile";
import { subscribeSanctionDialog } from "@/lib/sanctionDialog";
import { getCachedSessionUser, setSessionUser } from "@/lib/sessionUser";

const DIALOG_TYPES = new Set(["warning", "7-day", "custom", "permanent_ban", "ban_ended"]);

function dialogType(notice) {
  if (!notice?.type) return null;
  if (DIALOG_TYPES.has(notice.type)) return notice.type;
  return null;
}

function patchNotice(user, noticeId, patch) {
  if (!user) return user;

  const next = { ...user };
  if (next.sanction_notice?.id === noticeId) {
    next.sanction_notice = { ...next.sanction_notice, ...patch };
  }
  if (next.active_ban?.id === noticeId) {
    next.active_ban = { ...next.active_ban, ...patch };
  }
  return next;
}

// Pri prvo otvaranje, i koga korisnikot klikne na zapochna diskusija dokolku e baniran.
export default function SanctionNoticeHost() {
  const { user } = useProfile();
  const [pending, setPending] = useState(null);
  const [requested, setRequested] = useState(null);
  const [view, setView] = useState("notice");

  useEffect(() => {
    const type = dialogType(user?.sanction_notice);
    setPending(type ? user.sanction_notice : null);
  }, [user]);

  useEffect(() => subscribeSanctionDialog(setRequested), []);

  const notice = requested ?? pending;
  const type = dialogType(notice);
  const canAppeal = Boolean(notice?.can_appeal);
  const hasPendingAppeal = Boolean(notice?.has_pending_appeal);
  const showActions = type && type !== "ban_ended";

  function resetView() {
    setView("notice");
  }

  async function handleClose() {
    const id = notice?.id;
    setRequested(null);
    setPending(null);
    resetView();

    const cached = getCachedSessionUser();
    if (cached) {
      setSessionUser({ ...cached, sanction_notice: null });
    }

    if (id == null) return;

    try {
      await acknowledgeSanction(id);
    } catch {
      // Slednoto vchituvanje kje go pokaze povtorno ako serverot ne go zapishal.
    }
  }

  async function handleAppealSubmit(explanation) {
    const id = notice?.id;
    if (id == null) return;

    await submitAppeal(id, explanation);

    const patch = { can_appeal: false, has_pending_appeal: true };
    setPending((current) => (current?.id === id ? { ...current, ...patch } : current));
    setRequested((current) => (current?.id === id ? { ...current, ...patch } : current));

    const cached = getCachedSessionUser();
    if (cached) {
      setSessionUser(patchNotice(cached, id, patch));
    }
  }

  return (
    <>
      <SanctionDialog
        open={Boolean(type) && view === "notice"}
        type={type}
        expiresAt={notice?.expires_at}
        onClose={handleClose}
        onSeeReason={showActions ? () => setView("reason") : undefined}
        onAppeal={showActions ? () => setView("appeal") : undefined}
      />
      <SanctionReasonDialog
        open={Boolean(type) && view === "reason"}
        reason={notice?.reason}
        content={notice?.content}
        canAppeal={canAppeal || hasPendingAppeal}
        onAppeal={() => setView("appeal")}
        onClose={() => setView("notice")}
      />
      <SanctionAppealDialog
        open={Boolean(type) && view === "appeal"}
        canAppeal={canAppeal}
        pending={hasPendingAppeal}
        onSubmit={handleAppealSubmit}
        onClose={() => setView("notice")}
      />
    </>
  );
}
