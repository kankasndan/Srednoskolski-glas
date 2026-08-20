import InfoDialog from "@/components/ui/InfoDialog";

const TITLE = "Доби предупредување.";
const MESSAGE =
  "Твојата содржина ги прекрши правилата на заедницата. Те молиме внимавај при следното објавување, повторно прекршување ќе резултира со привремен бан од 7 дена.";

// Samo dizajnot; koga se prikazuva go povrzuva backendot.
export default function WarningDialog({ open, onClose }) {
  return (
    <InfoDialog
      open={open}
      title={TITLE}
      message={MESSAGE}
      messageWidthClassName="max-w-[324px]"
      onClose={onClose}
    />
  );
}
