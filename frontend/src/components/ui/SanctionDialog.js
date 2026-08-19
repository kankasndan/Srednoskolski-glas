import InfoDialog from "@/components/ui/InfoDialog";

// Klucevite se istite tipovi sankcii shto gi koristi backendot.
const SANCTIONS = {
  warning: {
    title: "Доби предупредување.",
    message:
      "Твојата содржина ги прекрши правилата на заедницата. Те молиме внимавај при следното објавување, повторно прекршување ќе резултира со привремен бан од 7 дена.",
  },
  "7-day": {
    title: "Имаш привремен бан.",
    message:
      "Поради повторно прекршување на правилата, не можеш да објавуваш и коментираш на содржини во следните 7 дена. По истекот на банот, повторно ќе можеш да учествуваш во дискусиите.",
  },
  permanent_ban: {
    title: "Твојот профил е трајно баниран.",
    message:
      "Поради повторени прекршувања на правилата, твојот профил е трајно баниран и повеќе не можеш да учествуваш на Средношколски Глас.",
  },
  // Ova ne e tip na sankcija, tuku porakata koga banot kje istece.
  ban_ended: {
    title: "Банот заврши!",
    message:
      "Твојот бан истече и повторно можеш да објавуваш дискусии и да коментираш.",
    note: "Те молиме почитувај ги правилата и придонесувај кон позитивна и безбедна заедница.",
  },
};

// Samo dizajnot; koga se prikazuva go povrzuva backendot.
export default function SanctionDialog({ open, type, onClose }) {
  const sanction = SANCTIONS[type];

  if (!sanction) return null;

  return (
    <InfoDialog
      open={open}
      title={sanction.title}
      message={sanction.message}
      note={sanction.note}
      messageWidthClassName="max-w-[324px]"
      onClose={onClose}
    />
  );
}
