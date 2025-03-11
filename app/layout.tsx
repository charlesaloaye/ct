import type { Metadata } from "next";
import "./globals.css";
import "bootstrap/dist/css/bootstrap.min.css";
import Footer from "@/components/Footer";
import Header from "@/components/Header";
import Script from "next/script";
import { Analytics } from "@vercel/analytics/react"

export const metadata: Metadata = {
  title: process.env.NEXT_PUBLIC_APP_NAME || "Charles Aloaye Sedenu",
  description: process.env.NEXT_PUBLIC_DESCRIPTION || "My Description",
};

export default function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <html lang='en'>
      <Analytics/>
      <body className={``}>
        <Header />
        <main className='wrapper'>{children}</main>
        <Footer />

        <Script
          src='https://code.jquery.com/jquery-3.6.0.min.js'
        />
         <Script
          src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js'
        />
      </body>
    </html>
  );
}
