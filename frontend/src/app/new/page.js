import AnonymousToggle from "@/components/AnonymousToggle";
import BackButton from "@/components/BackButton";
import ForumSelect from "@/components/ForumSelect";
import Header from "@/components/Header";
import PostTypeButtons from "@/components/PostTypeButtons";
import TitleInput from "@/components/TitleInput";

export default function NewDiscussionPage() {
  return (
    <div className="min-h-screen w-full bg-white">
      <Header />
      <div className="flex flex-col items-start gap-6 px-14">
        <BackButton label="Врати се назад" />
        <div className="flex w-[632px] max-w-full items-end gap-3">
          <ForumSelect />
          <AnonymousToggle className="flex-1" />
        </div>
        <TitleInput />
        <PostTypeButtons />
      </div>
    </div>
  );
}
