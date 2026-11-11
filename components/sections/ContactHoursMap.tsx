import { Container } from "@/components/layout/Container";

export interface ContactHoursRow {
  days: string;
  time: string;
}

export interface ContactHoursMapProps {
  eyebrow?: string;
  title: string;
  text?: string;
  hoursTitle: string;
  addressLabel: string;
  address: string;
  rows: ContactHoursRow[];
  mapSrc: string;
  mapTitle: string;
  hours?: "table" | "chips";
  map?: "bleed" | "framed";
}

/** Hours, a street address, and a map frame whose source comes from props. */
export function ContactHoursMap({
  eyebrow,
  title,
  text,
  hoursTitle,
  addressLabel,
  address,
  rows,
  mapSrc,
  mapTitle,
  hours = "table",
  map: mapPlace = "bleed",
}: ContactHoursMapProps) {
  const schedule = hours === "chips" ? (
    <ul className="flex flex-wrap gap-2">
      {rows.map((row) => (
        <li key={row.days} className="rounded-full bg-muted px-4 py-2 text-sm">
          <span className="font-medium">{row.days}</span>
          <span className="text-muted-foreground"> {row.time}</span>
        </li>
      ))}
    </ul>
  ) : (
    <table className="w-full text-left">
      <tbody>
        {rows.map((row) => (
          <tr key={row.days} className="border-b border-border">
            <th scope="row" className="py-3 pr-4 font-medium break-words">{row.days}</th>
            <td className="py-3 text-muted-foreground">{row.time}</td>
          </tr>
        ))}
      </tbody>
    </table>
  );

  const frame = (
    <iframe src={mapSrc} title={mapTitle} className="h-full min-h-80 w-full" loading="lazy" />
  );

  return (
    <section data-component-id="contact-hours-map" className="py-(--section-y)">
      <Container>
        <div className="grid gap-10 lg:grid-cols-2">
          <div className="min-w-0">
            {eyebrow && <p className="eyebrow mb-4 text-accent" data-editable-id="contact-hours-map.eyebrow">{eyebrow}</p>}
            <h2 className="text-headline break-words" data-editable-id="contact-hours-map.title">{title}</h2>
            {text && <p className="mt-5 text-lg break-words text-muted-foreground" data-editable-id="contact-hours-map.text">{text}</p>}
          </div>
          <div className="min-w-0">
            <h3 className="text-title break-words">{hoursTitle}</h3>
            <div className="mt-4">{schedule}</div>
            <p className="mt-8 text-sm text-muted-foreground">{addressLabel}</p>
            <p className="mt-1 break-words text-lg">{address}</p>
          </div>
        </div>
        {mapPlace === "framed" && (
          <div className="mt-10 overflow-hidden radius-xl border border-border bg-muted">{frame}</div>
        )}
      </Container>
      {mapPlace === "bleed" && <div className="mt-10 h-96 bg-muted">{frame}</div>}
    </section>
  );
}
