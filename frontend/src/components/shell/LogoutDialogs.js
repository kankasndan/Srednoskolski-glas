import ConfirmDialog from "@/components/ui/ConfirmDialog";
import InfoDialog from "@/components/ui/InfoDialog";

const CONFIRM_TITLE = "Дали си сигурен дека сакаш да се одјавиш од твојот профил?";
const DONE_TITLE = "Успешно се одјави од твојот профил.";
const DONE_MESSAGE = "Можеш да читаш и пребаруваш дискусии на платформата.";

export default function LogoutDialogs({ logout }) {
  return (
    <>
      <ConfirmDialog
        open={logout.confirming}
        title={CONFIRM_TITLE}
        cancelLabel="Назад"
        confirmLabel={logout.loggingOut ? "Се одјавува…" : "Одјави се"}
        onCancel={logout.cancel}
        onConfirm={logout.logout}
      />

      <InfoDialog
        open={logout.loggedOut}
        title={DONE_TITLE}
        message={DONE_MESSAGE}
        onClose={logout.finish}
      />
    </>
  );
}
