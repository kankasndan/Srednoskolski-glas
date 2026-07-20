import { Geist, Geist_Mono, Manrope, Oswald, Roboto } from "next/font/google";
import "./globals.css";
import NavigationTracker from "@/components/NavigationTracker";

const geistSans = Geist({
  variable: "--font-geist-sans",
  subsets: ["latin"],
});

const geistMono = Geist_Mono({
  variable: "--font-geist-mono",
  subsets: ["latin"],
});

const manrope = Manrope({
  variable: "--font-manrope",
  subsets: ["latin", "cyrillic"],
});

const oswald = Oswald({
  variable: "--font-oswald",
  subsets: ["latin", "cyrillic"],
});

const roboto = Roboto({
  variable: "--font-roboto",
  subsets: ["latin", "cyrillic"],
  weight: ["400", "500", "700"],
});

export const metadata = {
  title: "Средношколски глас",
  description: "Форум за средношколци — дискусии, форуми и заедница.",
};

export default function RootLayout({ children }) {
  return (
    <html
      lang="en"
      className={`${geistSans.variable} ${geistMono.variable} ${manrope.variable} ${oswald.variable} ${roboto.variable} h-full antialiased text-zinc-600`}
    >
      <body className="min-h-full flex flex-col">
        <NavigationTracker />
        {children}
      </body>
    </html>
  );
}