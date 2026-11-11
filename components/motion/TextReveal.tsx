"use client";

import { motion, useReducedMotion } from "motion/react";

/**
 * Headline that rises in word by word. Server and client render the same markup (no hydration mismatch);
 * reduced motion makes the transition instant.
 */
export function TextReveal({ text, as = "h1", className }: { text: string; as?: "h1" | "h2" | "p"; className?: string }) {
  const reduce = useReducedMotion();
  const Tag = as;
  const words = text.split(" ");
  return (
    <Tag className={className} aria-label={text}>
      {words.map((w, i) => (
        <span key={i} className="inline-block overflow-hidden pb-[0.08em] align-bottom" aria-hidden="true">
          <motion.span
            className="inline-block"
            initial={{ y: "105%" }}
            whileInView={{ y: 0 }}
            viewport={{ once: true }}
            transition={reduce ? { duration: 0 } : { duration: 0.7, ease: [0.22, 1, 0.36, 1], delay: i * 0.05 }}
          >
            {w}
            {i < words.length - 1 ? " " : ""}
          </motion.span>
        </span>
      ))}
    </Tag>
  );
}
