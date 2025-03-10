// 'use client'
import type { Metadata } from "next";
import "./globals.css";
import 'bootstrap/dist/css/bootstrap.min.css';
import Footer from "@/components/Footer";
import Header from "@/components/Header";


export const metadata: Metadata = {
  title: process.env.NEXT_PUBLIC_APP_NAME || 'Charles Aloaye Sedenu',
  description: process.env.NEXT_PUBLIC_DESCRIPTION || 'My Description'
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {

  return (
    <html lang="en">
      <body
        className={``}
      >
        <Header/>
        <main className="wrapper">
        {children}
        </main>
        <Footer/>
      </body>
    </html>
  );
}
