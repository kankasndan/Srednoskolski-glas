import { Geist, Geist_Mono, Manrope, Oswald, Roboto } from "next/font/google";
import "./globals.css";
import NavigationTracker from "@/components/shell/NavigationTracker";
import ImageProtection from "@/components/shell/ImageProtection";
import SanctionNoticeHost from "@/components/shell/SanctionNoticeHost";

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
  weight: "400",
  subsets: ["latin", "cyrillic"],
});

export const metadata = {
  title: "Средношколски глас",
  description: "Форум за средношколци — дискусии, форуми и заедница.",
  icons: {
    icon: "/logo.svg",
  },
};

export default function RootLayout({ children }) {
  return (
    <html
      lang="en"
      className={`${geistSans.variable} ${geistMono.variable} ${manrope.variable} ${oswald.variable} ${roboto.variable} h-full overflow-x-hidden antialiased text-zinc-600`}
    >
      <body className="flex h-full flex-col overflow-x-hidden">
        <NavigationTracker />
        <ImageProtection />
        <SanctionNoticeHost />
        {children}
      </body>
    </html>
  );
}
