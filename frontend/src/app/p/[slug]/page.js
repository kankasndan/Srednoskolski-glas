import { notFound } from "next/navigation";
import AppShell from "@/components/AppShell";
import ForumTopicContent from "@/components/ForumTopicContent";
import { FORUMS } from "@/lib/forums";

const DRZHAVNA_MATURA_SLUG = "drzhavna_matura";

export function generateStaticParams() {
  return FORUMS.filter((forum) => forum.slug !== DRZHAVNA_MATURA_SLUG).map((forum) => ({
    slug: forum.slug,
  }));
}

export default async function TopicForumPage({ params }) {
  const { slug } = await params;
  const forum = FORUMS.find((item) => item.slug === slug);

  if (!forum || slug === DRZHAVNA_MATURA_SLUG) {
    notFound();
  }

  return (
    <AppShell>
      <ForumTopicContent forumSlug={forum.slug} forumName={forum.name} />
    </AppShell>
  );
}
