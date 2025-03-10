// 'use client'
import type { Metadata } from "next";
import "./globals.css";
import "bootstrap/dist/css/bootstrap.min.css";
import Footer from "@/components/Footer";
import Header from "@/components/Header";
import Script from "next/script";
// import "bootstrap/dist/js/bootstrap.bundle.min.js";
// import "../public/js/custom.js";
// import "../public/js/dark-light.js";

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
      <body className={``}>
        <Header />
        <main className='wrapper'>{children}</main>
        <Footer />

        <Script
          src='https://code.jquery.com/jquery-3.6.0.min.js'
          strategy='beforeInteractive'
        />
        <Script
          src='https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js'
          strategy='beforeInteractive'
        />
        <Script
          src='https://cdnjs.cloudflare.com/ajax/libs/jquery-appear/0.1/jquery.appear.min.js'
          strategy='beforeInteractive'
        />
        <Script
          src='https://cdnjs.cloudflare.com/ajax/libs/ScrollIt/1.0.3/scrollIt.min.js'
          strategy='beforeInteractive'
        />
        <Script
          src='https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/jquery.magnific-popup.min.js'
          strategy='beforeInteractive'
        />
      </body>
    </html>
  );
}
